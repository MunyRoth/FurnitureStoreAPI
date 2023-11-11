<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Response;

class ProductController extends Controller
{


    public function searchByName($name)  {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Product $products): Response
    {
        return Response([
            'status' => 200,
            'message' => 'gotten successfully',
            'data' => $products->select('id', 'name', 'price', 'imageUrl')->get()
        ], 200);
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, Product $product): Response
    {
        $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'FurnitureStore'
        ])->getSecurePath();

        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        $image = new Image;
        $image->imageUrl = $imageUrl;
        $product->imageUrls()->save($image);

        return Response([
            'status' => 201,
            'message' => 'created successfully',
            'data' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): Response
    {
        $product = Product::find($id);
        if ($product) return Response([
            'status' => 200,
            'message' => 'gotten successfully',
            'data' => $product->load('imageUrls')
        ], 200);

        return Response([
            'status' => 404,
            'message' => 'not found',
            'data' => null
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id): Response
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

            return Response([
                'status' => 200,
                'message' => 'updated successfully',
                'data' => $product
            ], 200);
        }

        return Response([
            'status' => 404,
            'message' => 'not found',
            'data' => null
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): Response
    {
        $product = Product::find($id);

        if($product) {
            $product->delete();

            return Response([
                'status' => 200,
                'message' => 'deleted successfully',
                'data' => null
            ], 200);
        }

        return Response([
            'status' => 404,
            'message' => 'not found',
            'data' => null
        ], 404);
    }
}
