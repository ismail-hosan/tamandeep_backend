<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CMSController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Web\Backend\SystemSettingController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Public routes
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

// Cms Route
Route::post('/cms', [CMSController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {


    Route::controller(AuthController::class)->group(function () {
        Route::get('/check', 'check');
        Route::get('/logout', 'logout');
        Route::delete('/delete-account', 'deleteAccount');
        Route::post('/profile/update/user', 'ProfileUpdate');
    });

    Route::controller(ActionController::class)->group(function () {
        Route::post('/product/store', 'store');
        Route::get('/product/show', 'show');
    });





});
