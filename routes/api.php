<?php

use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CMSController;
use App\Http\Controllers\Api\QrcodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Cms Route
Route::post('/cms', [CMSController::class, 'index']);

Route::post('forget/password',[AuthController::class,'forgetPassword']);
Route::post('/verify/otp',[AuthController::class,'checkotp']);
Route::post('/password/update',[AuthController::class,'passwordUpdate']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/check', [AuthController::class, 'check']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/action/store',[ActionController::class, 'store']);
    Route::get('/action/show',[ActionController::class, 'show']);
    
});

Route::get('/user/view/{id}', [QrcodeController::class, 'view'])->name('user.view');
