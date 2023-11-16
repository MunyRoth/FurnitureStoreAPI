<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShoppingCartController;
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
    // Product Routes
    Route::resource('products', ProductController::class)->only([
        'store',
        'index',
        'show',
        'update',
        'destroy'
    ]);

    // Categories Routes
    Route::controller(CategoriesController::class)->group(function () {
        Route::get('/categories', 'retrieveAllCategories');
        Route::get('/categories_by_id/{id}', 'retrieveCategoryById');
    });

    // Custom ShoppingCart Routes
    Route::controller(ShoppingCartController::class)->group(function () {
        Route::get('/shoppingCartUnPaid', 'retrieveAllProductUnPaid');
        Route::get('/shoppingCartPaid', 'retrieveProductPaid');
        Route::put('/qtyOperation/{id}', 'qtyOperation');
        Route::get('/retrieveProductUnPaidById/{id}', 'retrieveProductUnPaidById');
        Route::post('/addProductToShoppingCart', 'addProductsToShoppingCart');
    });

    // Search Products Route
    Route::get('/search_product_by_name', [SearchController::class, 'searchProductByName']);
});

