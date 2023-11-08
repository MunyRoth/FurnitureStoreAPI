<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('guest')->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::post('/products', 'store');
        Route::get('/products', 'index');
        Route::get('/products/{id}', 'show');
        Route::put('/products/{id}', 'update');
        Route::delete('/products/{id}', 'destroy');
    });


    ///Categories Controller
    Route::controller(CategoriesController::class)->group(function () {
        Route::get('/categories', 'retrieveAllCategories');
        Route::get('/categories_by_id/{id}', 'retrieveCategoryById');
    });
});

