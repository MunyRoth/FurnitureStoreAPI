<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchProductByName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:15',
        ]);

        $products = Product::where('name', 'LIKE', '%' . $request->name . '%')->get();

        if (count($products) > 0) {
            return $this->Res($products, "product found", 200);
        } else {
            return $this->Res(null, "Product not found", 404);
        }
    }
}
