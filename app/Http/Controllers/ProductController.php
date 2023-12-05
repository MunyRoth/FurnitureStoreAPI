<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $products): JsonResponse
    {
        // Get the authenticated user, if any
        $user = auth()->guard('api')->user();

        // Determine the condition for the isFavorite column
        $isFavoriteCondition = $user ? 'IF(favourites.user_id = ' . $user->id . ' OR favourites.product_id IS NOT NULL, favourites.is_favourited, 0)' : '0';

        // Fetch data from the products table, including the isFavorite column
        $data = $products
            ->select('products.id', 'products.name', 'products.price', 'products.imageUrl', DB::raw($isFavoriteCondition . ' as isFavorite'))
            ->leftJoin('favourites', 'products.id', '=', 'favourites.product_id')
            ->get();

         // Return the response with the fetched data
         return $this->Res($data, 'got data successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, Product $product): JsonResponse
    {
        // Check if the product exists
        $existproduct = Product::where('name', $request->name)->first();
        if ($existproduct) {
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
        try {
            // Find the product by ID with its associated image URLs
            $product = Product::with('imageUrls', 'isFavorite')->findOrFail($id);
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

            // Update the product with the specified data
            $product->update($request->only(['name', 'price', 'description']));

            // Return the response with the updated product data
            return $this->Res($product, 'updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            // Return a not found response if the product is not found
            return $this->Res(null, 'not found', 404);
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
}
