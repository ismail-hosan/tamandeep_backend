<?php

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

 });
