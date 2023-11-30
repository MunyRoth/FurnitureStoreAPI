<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\HistoryController;
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
    // Register
    Route::post('register', [AuthController::class, 'register']);

    // Login
    Route::post('login', [AuthController::class, 'login']);

    // Product Routes
    Route::resource('products', ProductController::class)->only([
        'store',
        'index',
        'show',
        'update',    
    ]);

    Route::get('retrieveProducts', [ProductController::class, 'retrieveProducts']);
    Route::resource('history', HistoryController::class)->only([
        'store',
        'index',
        'show',
        'update',    
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
        Route::put('/qtyOperation', 'qtyOperation');
        Route::get('/retrieveProductUnPaidById/{id}', 'retrieveProductUnPaidById');
        Route::post('/addProductToShoppingCart', 'addProductsToShoppingCart');
        Route::delete('/deleteProductCart/{id}', 'deleteProductCartById');
    });

    // Search Products Route
    Route::get('/search_product_by_name', [SearchController::class, 'searchProductByName']);
});

Route::middleware('auth:api')->group(function () {
    // Logout
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('loadProfile', [AuthController::class, 'getProfile']);

    Route::put('updateProfile', [AuthController::class, 'store']);

    // Product Routes
    Route::resource('products', ProductController::class)->only([
        'destroy'
    ]);

    // Favourite Routes
    Route::resource('favorite', FavouriteController::class)->only([
        'index',
        'store'
    ]);
});