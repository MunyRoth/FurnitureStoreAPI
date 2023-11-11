<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductToShoppingCart;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{

    //function Add Product To Shopping Cart
    public function addProductsToShoppingCart(AddProductToShoppingCart $request)
    {
        $shoppingCart = new ShoppingCart();
        //check product have on db or not
        $check = Product::find($request['product_id']);
        if (!$check){
            return $this->Res(null, "The product is not yet existed.", 200);
        }
        //check the product already has on shopping cart table or not
        $checkProductHaveAddorNot = $shoppingCart->firstWhere('product_id', $request['product_id']);
        if($checkProductHaveAddorNot){
            return $this->Res(null, "The product is existed add.", 200);
        }
        //if product not yet has add it to shopping cart
        $shoppingCart->product_id = $request['product_id'];
        $shoppingCart->paid = 0;
        $shoppingCart->qty = 1;
        $shoppingCart->save();
        return $this->Res($checkProductHaveAddorNot, "add product to shoppincart successfully", 200);
    }

    //query product that not yet paid
    public function retrieveAllProductUnPaid()
    {

        $data = ShoppingCart::where('paid', 0)->with('product')->get();
        // $data = ShoppingCart::where('paid', 0)->get();

        return $this->Res($data, "gotten successfully", 200);
    }

    //query product that already paided
    public function retrieveProductPaid()
    {
        $data = ShoppingCart::where("paid", 1)->with('product')->get();
        return $this->Res($data, "gotten successfully", 200);
    }


    public function retrieveProductUnPaidById($id)
    {

        $data = ShoppingCart::find($id)->with('product')->get();
        return $this->Res($data, "gotten successfully", 200);
    }

    //increase or decrease Quantity
    public function qtyOperation(Request $request, $id)
    {
        $data = ShoppingCart::find($id);
        if ($data) {
            if ($request->qty > $data->qty) {
                $data->qty++;
                $data->save();
                return $this->Res("product id: $data->product_id", 'Increase Qty done', 200);
            } else if ($request->qty < $data->qty) {
                $data->qty--;
                $data->save();
                return $this->Res("product id: $data->product_id", 'Decrease Qty done', 200);
            } else return $this->Res("product id: $data->product_id", 'Qty still not change', 200);
        } else return $this->Res(null, "This product cannot found.", 200);
    }
}
