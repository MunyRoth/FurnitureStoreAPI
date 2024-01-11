<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
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

    // Social login
    Route::get('auth/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

    // Verify email by clicking on the link
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verifyEmailByLink'])
        ->middleware('signed')
        ->name('verification.verify');
    // Verify email by using OTP
    Route::post('email/verify/usingOTP', [VerificationController::class, 'verifyEmailByOTP'])
        ->middleware('throttle:6,1');

    // Resend OTP to verify email
    Route::post('email/verify/resendOTP', [VerificationController::class, 'resendOTP'])
        ->middleware('throttle:6,1');

    // Forgot password
    Route::post('password/forgot', [PasswordController::class, 'forgotPassword']);
    Route::post('password/reset', [PasswordController::class, 'resetPassword']);
    Route::get('password/check', [PasswordController::class, 'checkOtp'])
        ->name('password.check');
    Route::post('password/verifyOTP', [PasswordController::class, 'verifyOTP']);

    // Product Routes
    Route::resource('products', ProductController::class)->only([
        'index',
        'show',
    ]);

    // Categories Routes
    Route::controller(CategoriesController::class)->group(function () {
        Route::get('/categories', 'retrieveAllCategories');
        Route::get('/categories_by_id/{id}', 'retrieveCategoryById');
    });

    // Search Products Route
    Route::get('/search_product_by_name', [SearchController::class, 'searchProductByName']);
});

Route::middleware('auth:api')->group(function () {
    // Logout
    Route::get('logout', [AuthController::class, 'logout']);
    // Get user Profile
    Route::get('loadProfile', [AuthController::class, 'getProfile']);
    // Update user Profile
    Route::put('updateProfile', [AuthController::class, 'store']);
    // Change user password
    Route::post('password/change', [PasswordController::class, 'changePassword']);
    // Resend link to verify email
    Route::post('email/verify/resend', [VerificationController::class, 'resendEmail'])
        ->middleware('throttle:6,1');

    // Categories Routes
    Route::resource('categories', CategoriesController::class)->only([
        'store',
        'update',
        'destroy'
    ]);

    // Product Routes
    Route::resource('products', ProductController::class)->only([
        'store',
        'update',
        'destroy'
    ]);

    Route::post('products/{id}/uploadImage', [ProductController::class, 'uploadImage']);

    // Custom ShoppingCart Routes
    Route::controller(ShoppingCartController::class)->group(function () {
        Route::get('/shoppingCartUnPaid', 'retrieveAllProductUnPaid');
        Route::get('/shoppingCartPaid', 'retrieveProductPaid');
        Route::put('/qtyOperation', 'qtyOperation');
        Route::get('/retrieveProductUnPaidById/{id}', 'retrieveProductUnPaidById');
        Route::post('/addProductToShoppingCart', 'addProductsToShoppingCart');
        Route::delete('/deleteProductCart/{id}', 'deleteProductCartById');
    });

    // Payment Methods Routes
    Route::resource('history', HistoryController::class)->only([
        'index',
        'store',
        'show',
        'update',
    ]);

    Route::delete('history', [HistoryController::class, 'destroy']);


    // Favourite Routes
    Route::resource('favorite', FavouriteController::class)->only([
        'index',
        'store'
    ]);
});
