<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductToShoppingCart;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ShoppingCartController extends Controller
{
    // Add Product To Shopping Cart
    public function addProductsToShoppingCart(AddProductToShoppingCart $request)
    {
        $user_id = Auth::id();

        // Create a new instance of ShoppingCart
        $shoppingCart = new ShoppingCart();
        // Check if the product exists in the database
        try {
            Product::findOrFail($request['product_id']);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, "The product is not yet existed.", 404);
        }

        // Check if the product is already in the shopping cart
        $checkProductHaveAddorNot = ShoppingCart::where("user_id", $user_id)->where('product_id', $request['product_id'])->exists();

        if ($checkProductHaveAddorNot) {
            // Return a response if the product is already in the shopping cart
            return $this->Res(null, "The product is existed add.", 409);
        }

        // Add the product to the shopping cart
        $shoppingCart->user_id = $user_id;
        $shoppingCart->product_id = $request['product_id'];
        $shoppingCart->paid = 0;
        $shoppingCart->qty = 1;
        $shoppingCart->save();

        // Return a success response
        return $this->Res(null, "Add product to shopping cart successfully", 200);
    }

    // Retrieve all products that are not yet paid
    public function retrieveAllProductUnPaid()
    {
        // Retrieve all products that belong to user and are not yet paid with their associated products
        $data = ShoppingCart::where("user_id", Auth::id())->where("paid", 0)->with('product')->get();

        // Return the data with a success message
        return $this->Res($data, "Products retrieved successfully", 200);
    }

    // Retrieve products that are already paid
    public function retrieveProductPaid()
    {
        // Retrieve products that are already paid with their associated products
        $data = ShoppingCart::where("user_id", Auth::id())->where("paid", 1)->with('product')->get();

        // Return the data with a success message
        return $this->Res($data, "Products retrieved successfully", 200);
    }


    // Retrieve a product that is not yet paid by its ID
    public function retrieveProductUnPaidById($id)
    {
        // Retrieve a product that is not yet paid by its ID with its associated product

        $data = ShoppingCart::where("user_id", Auth::id())->where("paid", 0)->where("id", $id)->with('product')->first();

        // $data = ShoppingCart::findOrFail($id)->with('product')->get();

        // Return the data with a success message
        return $this->Res($data, "Product retrieved successfully", 200);
    }

    //increase or decrease Quantity
    public function qtyOperation(Request $request)
    {

        try {
            // Assuming you're using Laravel's Auth facade to get the authenticated user's ID
            $user_id = Auth::id();
            // Get the request data as an array
            $requests = $request->json()->all();

            // Check if $requests is an array of requests or a single request
            if (!is_array(reset($requests))) {
                // If it's a single request, convert it to an array of requests
                $requests = [$requests];
            }

            // Extract an array of product IDs
            $product_ids = array_column($requests, 'id');

            // Find all shopping cart items with the specified product IDs
            $data = ShoppingCart::whereIn('product_id', $product_ids)->where("user_id", $user_id)->get();

            // Iterate over each shopping cart item and update the quantity
            $data->each(function ($item) use ($requests) {
                $product_id = $item->product_id;

                // Find the corresponding request data for the current product ID
                $request_data = collect($requests)->firstWhere('id', $product_id);

                //if qty > 0 qty can be update
                if ($request_data && $request_data['qty'] > 0) {
                    // Update the quantity for the current shopping cart item
                    $item->update(['qty' => $request_data['qty']]);
                }
            });

            // Return a success response
            return $this->Res(null, 'Quantity updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, "One or more products cannot be found.", 404);
        }
    }


    public function deleteProductCartById($id)
    {
        $data = ShoppingCart::where('user_id', Auth::id())->where("id", $id)->get();

        if ($data->isEmpty()) {
            return $this->Res(null, 'Data is not found.', 404);
        }

        // Assuming there's only one record, you can use first() to get it
        $data->first()->delete();

        return $this->Res(null, "Delete Done", 200);
    }
}
