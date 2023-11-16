<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavouriteRequest;
use App\Models\Favourite;
use App\Models\Product;
use Illuminate\Database\Query\JoinClause;

class FavouriteController extends Controller
{
    // Get All Favourites
    public function index() {
        $products = Product::query()
        ->join('favourites', function (JoinClause $join) {
            $join->on('products.id', '=', 'favourites.product_id') // Adjust this based on your actual relationship
                 ->where('favourites.user_id', '=', auth()->user()->id)
                 ->where('favourites.is_favourited', '=', 1);
        })
        ->select('products.id', 'products.name', 'products.price', 'products.imageUrl')
        ->get();
    
        return $this->Res($products, 'Product received successfully', 200);
    }
    
    // Create a new Favourite
    public function store(FavouriteRequest $request, Favourite $favourite)
    {
        // Ensure the user is authenticated
        $user = auth()->guard('api')->user();

        // Check if the product exists
        $product = Product::find($request->product_id);
        if (!$product) {
            return $this->Res(null, 'Product not found', 404);
        }

        // Check if the user has already favorited/unfavorited the product
        $isExists = Favourite::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($isExists) {
            // Toggle the favorite status
            $isExists->update([
                'is_favourited' => !$isExists->is_favourited
            ]);

            return $this->Res($isExists->is_favourited, 'Updated successfully', 200);
        }

        // Create a new favorite record
        $favourite->user_id = $user->id;
        $favourite->product_id = $request->product_id;
        $favourite->is_favourited = true;
        $favourite->save();

        return $this->Res(true, 'Created successfully', 201);
    }
}
