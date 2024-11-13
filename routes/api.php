<?php

use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CMSController;
use App\Http\Controllers\Api\QrcodeController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Cms Route
Route::post('/cms', [CMSController::class, 'index']);



// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/check', [AuthController::class, 'check']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/product/store',[ActionController::class, 'store']);
    Route::get('/product/show',[ActionController::class, 'show']);
});

Route::get('/user/view/{id}', [QrcodeController::class, 'view'])->name('user.view');
