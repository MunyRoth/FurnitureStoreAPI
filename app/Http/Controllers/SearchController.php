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

        // Use the isEmpty method to check if the collection is empty
        if ($products->isEmpty()) {
            return $this->Res(null, "Product not found", 404);
        }

        return $this->Res($products, "Product found", 200);
    }
}
