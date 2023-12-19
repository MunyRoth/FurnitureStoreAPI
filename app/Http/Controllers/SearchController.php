<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchProductByName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:15',
        ]);

        // Get the query parameters for pagination
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);

        $data = Product::where('name', 'ILIKE', '%' . $request->name . '%')
            ->paginate($size, ['*'], 'page', $page);

        // Use the isEmpty method to check if the collection is empty
        if ($data->isEmpty()) {
            return $this->Res(null, "Product not found", 404);
        }

        return $this->Res(
            $data->items(),
            'got data successfully',
            200,
            $data->currentPage(),
            $data->perPage(),
            $data->total()
        );
    }
}
