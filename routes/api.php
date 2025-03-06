<?php

use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CMSController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\QrcodeController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Cms Route
Route::post('/cms', [CMSController::class, 'index']);
Route::get('/cms/feature', [CMSController::class, 'feature']);
Route::get('cms/review', [CMSController::class, 'review']);
Route::get('cms/brand', [CMSController::class, 'brand']);
Route::get('faq/all',[FaqController::class,'index']);
Route::post('contact_us',[ContactController::class,'store']);



Route::post('forget/password', [AuthController::class, 'forgetPassword']);
Route::post('/verify/otp', [AuthController::class, 'checkotp']);
Route::post('/password/update', [AuthController::class, 'passwordUpdate']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/check', [AuthController::class, 'check']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/update', [AuthController::class, 'profileUpdate']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/action/store', [ActionController::class, 'store']);
    Route::get('/action/show/{id}', [ActionController::class, 'show']);
    Route::post('/action/status', [ActionController::class, 'status']);
    Route::post('/action/delete', [ActionController::class, 'delete']);
    Route::post('/action/update', [ActionController::class, 'update']);
    Route::post('/action/edit', [ActionController::class, 'edit']);
    Route::get('/taps/ditails/{id}', [QrcodeController::class, 'tapsData']);
    Route::get('/contact/get/{id}', [ContactController::class, 'contactShow']);
   


    Route::get('/user/card', [UserController::class, 'index']);
    Route::post('/user/card/status', [UserController::class, 'status']);

 

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/create', [CartController::class, 'store']);
        Route::post('/quantity', [CartController::class, 'quantity']);
        Route::post('/delete', [CartController::class, 'delete']);
    });

    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout.create');
    Route::post('/subscription/{type}', [SubscriptionController::class, 'createSubscriptionSession'])->name('checkout.subscriptions');



});

Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

// Route for canceled payment
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

Route::prefix('card')->group(function () {
    Route::get('/', [CardController::class, 'index']);
});

// CMS Route
Route::post('/contact/add', [ContactController::class, 'addContact'])->name('user.view');
Route::post('/paypal/add', [ContactController::class, 'addPaypal'])->name('user.paypal');
Route::get('/action/view/{code}', [QrcodeController::class, 'view'])->name('user.view');
Route::post('stripe/webhook', [StripeController::class, 'handle']);