<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
         //Retrieve all categories data
         public function retrieveAllCategories() {
            $data = Category::all();
            if (!$data){
                return $this->Res($data, 'data is empty', 200);  
            }
            return $this->Res($data, 'gotten successfully', 200);  
        }
    
    //Retrieve products that belong to category 
    public function retrieveCategoryById($id){
        $data = Category::find($id)->load('products');
        if (!$data){
            return $this->Res($data, 'data is empty', 200);  
        }
        return $this->Res($data, 'gotten successfully', 200);  
    }


}
