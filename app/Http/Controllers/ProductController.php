<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{


    public function searchByName($name)  {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Product $products)
    {
        $data = $products->select('id', 'name', 'price', 'imageUrl')->get();
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

        $product->name = $request->name;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->description = $request->description;
        $product->imageUrl = $imageUrl;
        $product->save();

        $image = new Image;
        $image->product_id = $product->id;
        $image->imageUrl = $product->imageUrl;
        $image->save();

        return $this->Res(
            $product,
            'created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);
        if ($product) return $this->Res(
            $product->load('imageUrls'),
            'gotten successfully',
            200
        );

        return $this->Res(
            null,
            'not found',
            404
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);

        if($product) {
            if ($request->name != '') {
                $product->update([
                    'name' => $request->name
                ]);
            }

            if ($request->price != '') {
                $product->update([
                    'price' => $request->price
                ]);
            }

            if ($request->description != '') {
                $product->update([
                    'description' => $request->description
                ]);
            }


            return $this->Res(
                $product,
                'updated successfully',
                200
            );
        }

        return $this->Res(
            null,
            'not found',
            404
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if($product) {
            $product->delete();

            return $this->Res(
                null,
                'deleted successfully',
                200
            );
        }

        return $this->Res(
            null,
            'not found',
            404
        );
    }
}
