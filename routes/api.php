<?php

use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CMSController;
use App\Http\Controllers\Api\QrcodeController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Cms Route
Route::post('/cms', [CMSController::class, 'index']);

Route::post('forget/password', [AuthController::class, 'forgetPassword']);
Route::post('/verify/otp', [AuthController::class, 'checkotp']);
Route::post('/password/update', [AuthController::class, 'passwordUpdate']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/check', [AuthController::class, 'check']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/action/store', [ActionController::class, 'store']);
    Route::get('/action/show/{id}', [ActionController::class, 'show']);
    Route::get('/action/status/{id}', [ActionController::class, 'status']);
    Route::post('/action/delete', [ActionController::class, 'delete']);
    Route::post('/action/update', [ActionController::class, 'update']);


    Route::get('/user/card', [UserController::class, 'index']);
    Route::post('/user/card/status', [UserController::class, 'status']);



    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/create', [CartController::class, 'store']);
        Route::post('/quantity', [CartController::class, 'quantity']);
        Route::post('/delete', [CartController::class, 'delete']);
    });

    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout.create');



});

Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

// Route for canceled payment
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

Route::prefix('card')->group(function () {
    Route::get('/', [CardController::class, 'index']);
});

Route::get('/user/view/{id}', [QrcodeController::class, 'view'])->name('user.view');
Route::post('stripe/webhook', [StripeController::class, 'handle']);