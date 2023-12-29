<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $products, Request $request): JsonResponse
    {
        // Get the authenticated user, if any
        $user = auth()->guard('api')->user();
        $id = $user?->id;

        // Get the query parameters for pagination and search
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);
        $search = $request->query('search');

        // Enable query logging
        DB::enableQueryLog();

        // Method 1
        // Determine the condition for the isFavorite column
        $isFavoriteCondition = $id ? 'IF(favourites.user_id = ' . $id . ' AND favourites.product_id IS NOT NULL, favourites.is_favourited, 0)' : '0';

        // Fetch data from the products table, including the isFavorite column
        $data = $products
            ->select('products.id', 'products.name', 'products.price', 'products.imageUrl', DB::raw($isFavoriteCondition . ' as isFavorite'))
            ->leftJoin('favourites', function ($join) use ($products, $id) {
                $join->on('favourites.product_id', '=', 'products.id')
                    ->where('favourites.user_id', '=', $id);
            })
            ->where('products.name', 'LIKE', '%' . $search . '%')
            ->orderByDesc('products.updated_at')
            ->paginate($size, ['*'], 'page', $page);

        // Method 2
//        $products = Product::get();
//
//        foreach ($products as $product) {
//            $product->isFavorite = (bool) $product->isFavorite()->where('user_id', $id)->first();
//        }

        // Method 3
        // Retrieve products with eager loading of the isFavorite relationship
//        $products = Product::with(['isFavorite' => function ($query) use ($id) {
//            $query->where('user_id', $id);
//        }])->get();
//
//        // Set the isFavorite property for each product
//        $products->each(function ($product) use ($id) {
//            // Check if the relationship exists before accessing it
//            $product->isFavorite = $product->isFavorite && $product->isFavorite->first();
//        });

        // Get the executed queries
        $queries = DB::getQueryLog();

        // Log the queries (for debugging purposes)
        Log::info('Executed Queries: ', $queries);

        // Disable query logging to prevent interference with subsequent queries
        DB::disableQueryLog();

        // Return the response with the fetched data
        return $this->Res(
            $data->items(),
            'got data successfully',
            200,
            $data->currentPage(),
            $data->perPage(),
            $data->total()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, Product $product): JsonResponse
    {
        // Check if the product exists
        $existProduct = Product::where('name', $request->name)->first();
        if ($existProduct) {
            return $this->Res($product, 'Product already exists', 409);
        }

        // Upload the image to Cloudinary
        $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'FurnitureStore'
        ])->getSecurePath();

        // Fill the product model with the request data
        $product->fill($request->only(['name', 'price', 'category_id', 'description']));
        $product->imageUrl = $imageUrl;
        $product->save();

        // Create and save a new image record associated with the product
        $image = new Image;
        $image->product_id = $product->id;
        $image->imageUrl = $product->imageUrl;
        $image->save();

        // Return the response with the created product data
        return $this->Res($product, 'created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        // Get the authenticated user, if any
        $user = auth()->guard('api')->user();

        try {
            if ($user) {
                // Find the product by ID with its associated image URLs and the isFavorite column for the authenticated user
                $product = Product::with(['imageUrls', 'isFavorite' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])->findOrFail($id);
            } else {
                // Find the product by ID with its associated image URLs (without isFavorite information for non-authenticated users)
                $product = Product::with('imageUrls')->findOrFail($id);
            }
            return $this->Res($product, 'gotten successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, 'not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        try {
            // Find the product by ID
            $product = Product::findOrFail($id);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {

                // Validate file type and size if needed
                $request->validate([
                    'image' => 'image|mimes:jpeg,png,jpg,gif|max:8048',
                ]);

                // Upload the image to Cloudinary
                $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'FurnitureStore'
                ])->getSecurePath();

                $product->imageUrl = $imageUrl;
                $product->save();
            }

            // Update the product with the specified data
            $product->update($request->only(['name', 'price', 'description']));

            // Return the response with the updated product data
            return $this->Res($product, 'updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            // Return a not found response if the product is not found
            return $this->Res(null, 'not found', 404);
        } catch (Exception $e) {
            // Handle exceptions, log errors, or return an appropriate response
            return $this->Res(null, $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Find the product by ID and delete it
            $product = Product::findOrFail($id);
            $product->delete();

            // Return a success response
            return $this->Res(null, 'deleted successfully', 200);
        } catch (ModelNotFoundException $e) {
            // Return a not found response if the product is not found
            return $this->Res(null, 'not found', 404);
        }
    }

    /**
     * Upload the specified image to Cloudinary.
     */
    public function uploadImage(Request $request, $id): JsonResponse
    {
        try {
            // Find the product by ID
            $product = Product::findOrFail($id);

            // Validate file type and size if needed
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:8048',
            ]);

            // Upload the image to Cloudinary
            $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'FurnitureStore'
            ])->getSecurePath();

            // Create and save a new image record associated with the product
            $image = new Image;
            $image->product_id = $id;
            $image->imageUrl = $imageUrl;
            $image->save();

            // Return the response with the updated product data
            return $this->Res($product, 'updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            // Return a not found response if the product is not found
            return $this->Res(null, 'not found', 404);
        } catch (Exception $e) {
            // Handle exceptions, log errors, or return an appropriate response
            return $this->Res(null, $e->getMessage(), 500);
        }
    }
}
