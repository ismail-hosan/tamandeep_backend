<?php

use App\Http\Controllers\Web\Backend\BrandlogoController;
use App\Http\Controllers\Web\Backend\PlanPackageController;
use App\Http\Controllers\Web\Backend\ReviewController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth','verified'])->group(function () {

     // Route for ReviewController
     Route::prefix('review')->controller(ReviewController::class)->group(function () {
         Route::get('/', 'index')->name('review.index');
         Route::get('/create', 'create')->name('review.create');
         Route::post('/store', 'store')->name('review.store');
         Route::get('/edit/{id}', 'edit')->name('review.edit');
         Route::post('/update/{id}', 'update')->name('review.update');
         Route::delete('/delete/{id}', 'destroy')->name('review.delete');
         Route::get('/status/{id}',  'status')->name('review.status');
     });

     // Route for BrandlogoController
     Route::prefix('brandlogo')->controller(BrandlogoController::class)->group(function () {
        Route::get('/', 'index')->name('brandlogo.index');
        Route::get('/create', 'create')->name('brandlogo.create');
        Route::post('/store', 'store')->name('brandlogo.store');
        Route::get('/edit/{id}', 'edit')->name('brandlogo.edit');
        Route::post('/update/{id}', 'update')->name('brandlogo.update');
        Route::delete('/delete/{id}', 'destroy')->name('brandlogo.delete');
        Route::get('/status/{id}',  'status')->name('brandlogo.status');
    });

    // Route for PlanPackageController
    Route::prefix('planpackage')->controller(PlanPackageController::class)->group(function () {
        Route::get('/', 'index')->name('planpackage.index');
        Route::get('/create', 'create')->name('planpackage.create');
        Route::post('/store', 'store')->name('planpackage.store');
        Route::get('/edit/{id}', 'edit')->name('planpackage.edit');
        Route::post('/update/{id}', 'update')->name('planpackage.update');
        Route::delete('/delete/{id}', 'destroy')->name('planpackage.delete');
        Route::get('/status/{id}',  'status')->name('planpackage.status');
    });

 });
