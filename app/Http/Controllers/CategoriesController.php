<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    // Retrieve all categories data
    public function retrieveAllCategories()
    {
        // Fetch all categories from the database
        $data = Category::all();

        // Check if categories data is empty
        if (!$data) {
            return $this->Res($data, 'Data is empty', 200);
        }

        // Return a successful response with categories data
        return $this->Res($data, 'Data retrieved successfully', 200);
    }

    // Retrieve products that belong to a specific category
    public function retrieveCategoryById($id)
    {
        // Find the category by ID and eager load its products
        $data = Category::find($id)->load('products');

        // Check if category data is empty
        if (!$data) {
            return $this->Res($data, 'Data is empty', 200);
        }

        // Return a successful response with the category and its products
        return $this->Res($data, 'Data retrieved successfully', 200);
    }
}
