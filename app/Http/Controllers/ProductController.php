<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    public function searchByName($name)
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Product $products)
    {
        $data = $products
            ->select('products.id', 'products.name', 'products.price', 'products.imageUrl', 'favourites.is_favourited')
            ->leftJoin('favourites', 'products.id', '=', 'favourites.product_id')
            ->get();
    
        return $this->Res($data, "got data successfully", 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, Product $product)
    {
        $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'FurnitureStore'
        ])->getSecurePath();

        $product->fill($request->only(['name', 'price', 'category_id', 'description']));
        $product->imageUrl = $imageUrl;
        $product->save();

        $image = new Image;
        $image->product_id = $product->id;
        $image->imageUrl = $product->imageUrl;
        $image->save();

        return $this->Res($product, 'created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $product = Product::with('imageUrls')->findOrFail($id);
            return $this->Res($product, 'gotten successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, 'not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update($request->only(['name', 'price', 'description']));
            return $this->Res($product, 'updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, 'not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return $this->Res(null, 'deleted successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, 'not found', 404);
        }
    }
}
