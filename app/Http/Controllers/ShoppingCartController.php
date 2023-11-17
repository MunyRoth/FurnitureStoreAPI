<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductToShoppingCart;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    // Add Product To Shopping Cart
    public function addProductsToShoppingCart(AddProductToShoppingCart $request)
    {
        // Create a new instance of ShoppingCart
        $shoppingCart = new ShoppingCart();

        // Check if the product exists in the database
        try {
            Product::findOrFail($request['product_id']);
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, "The product is not yet existed.", 404);
        }

        // Check if the product is already in the shopping cart
        $checkProductHaveAddorNot = $shoppingCart->firstWhere('product_id', $request['product_id']);
        if ($checkProductHaveAddorNot) {
            // Return a response if the product is already in the shopping cart
            return $this->Res(null, "The product is existed add.", 409);
        }

        // Add the product to the shopping cart
        $shoppingCart->product_id = $request['product_id'];
        $shoppingCart->paid = 0;
        $shoppingCart->qty = 1;
        $shoppingCart->save();

        // Return a success response
        return $this->Res($checkProductHaveAddorNot, "Add product to shopping cart successfully", 200);
    }

    // Retrieve all products that are not yet paid
    public function retrieveAllProductUnPaid()
    {
        // Retrieve all products that are not yet paid with their associated products
        $data = ShoppingCart::where('paid', 0)->with('product')->get();

        // Return the data with a success message
        return $this->Res($data, "Products retrieved successfully", 200);
    }

    // Retrieve products that are already paid
    public function retrieveProductPaid()
    {
        // Retrieve products that are already paid with their associated products
        $data = ShoppingCart::where("paid", 1)->with('product')->get();

        // Return the data with a success message
        return $this->Res($data, "Products retrieved successfully", 200);
    }


    // Retrieve a product that is not yet paid by its ID
    public function retrieveProductUnPaidById($id)
    {
        // Retrieve a product that is not yet paid by its ID with its associated product
        $data = ShoppingCart::findOrFail($id)->with('product')->get();

        // Return the data with a success message
        return $this->Res($data, "Product retrieved successfully", 200);
    }

    // // Increase or decrease quantity
    // public function qtyOperation(Request $request, $id)
    // {
    //     try {
    //         // Find the shopping cart record by ID
    //         $data = ShoppingCart::findOrFail($id);

    //         // Check if the requested quantity is greater than the current quantity
    //         if ($request->qty > $data->qty) {
    //             $data->qty++;
    //             $data->save();
    //             // Return a success response if quantity is increased
    //             return $this->Res("Product ID: $data->product_id", 'Increase Qty done', 200);
    //         } elseif ($request->qty < $data->qty) {
    //             $data->qty--;
    //             $data->save();
    //             // Return a success response if quantity is decreased
    //             return $this->Res("Product ID: $data->product_id", 'Decrease Qty done', 200);
    //         } else {
    //             // Return a response if quantity remains unchanged
    //             return $this->Res("Product ID: $data->product_id", 'Qty still not changed', 200);
    //         }
    //     } catch(ModelNotFoundException $e) {
    //         // Return a response if the product is not found
    // return $this->Res(null, "This product cannot be found.", 404);
    //     }
    // }
    //increase or decrease Quantity
    public function qtyOperation(Request $request, $id)
    {

        try {
            $data = ShoppingCart::findOrFail($id);;
            if ($request->qty > $data->qty) {
                $data->qty = $request->qty;
                $data->save();
                 // Return a success response if quantity is increased
                return $this->Res("product id: $data->product_id", 'Increase Qty done', 200);
            } else if ($request->qty < $data->qty) {
                $data->qty = $request->qty;
                $data->save();
                 // Return a success response if quantity is decreased
                return $this->Res("product id: $data->product_id", 'Decrease Qty done', 200);        
            }else {
                  // Return a response if quantity remains unchanged
                  return $this->Res("Product ID: $data->product_id", 'Qty still not changed', 200);
            }
        } catch (ModelNotFoundException $e) {
            return $this->Res(null, "This product cannot be found.", 404);
        }
    }


    public function deleteProductCartById($id)
    {
        $data = ShoppingCart::find($id);
        if (!$data) {
            return $this->Res(null, 'Data is not found.', 404);
        }
        $data->delete();
        return $this->Res(null, "Delete Done", 200);
    }
}
