<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    // Create a new category
    public function store(Request $request): JsonResponse
    {
        // Validate request parameters
        $this->validate($request, [
            'name' => 'required|string',
        ]);

        // Create or retrieve the category
        $category = Category::firstOrCreate(['name' => $request->name]);

        // Return a successful response message
        return $this->Res($category, 'Category created successfully', 201);
    }

    // Update an existing category
    public function update(Request $request, Category $category): JsonResponse
    {
        // Validate request parameters
        $this->validate($request, [
            'name' => 'string',
        ]);

        $category->name = $request->name;
        $category->save();

        // Return a successful response message
        return $this->Res($category, 'Category updated successfully', 200);
    }

    // Delete an existing category
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        // Return a successful response message
        return $this->Res($category, 'Category deleted successfully', 200);
    }

    // Retrieve all categories data
    public function retrieveAllCategories(): JsonResponse
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
    public function retrieveCategoryById($id): JsonResponse
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
