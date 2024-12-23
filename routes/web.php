<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Backend\CMSController;
use App\Http\Controllers\Web\Backend\DahboardController;
use App\Http\Controllers\Web\Backend\FeaturesController;
use App\Http\Controllers\Web\Backend\QrcodeController;
use App\Http\Controllers\Web\Backend\SystemSettingController;
use App\Http\Controllers\Web\Backend\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return response()->json(['message' => 'All caches cleared successfully']);
})->name('optimize.clear');


Route::get('/dashboard',[DahboardController::class,'index'])->name('dashboard')->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth','verified'])->group(function () {
   Route::controller(SystemSettingController::class)->group(function () {
        Route::get('/system-setting', 'index')->name('system.setting');
        Route::post('/system-setting', 'update')->name('system.update');
        Route::get('/system/mail', 'mailSetting')->name('system.mail.index');
        Route::post('/system/mail', 'mailSettingUpdate')->name('system.mail.update');
        Route::get('/system/profile', 'profileindex')->name('profilesetting');
        Route::get('/system/stripe', 'stripeindex')->name('stripe.index');
        Route::post('/system/stripe', 'stripestore')->name('stripe.store');
        Route::get('/system/paypal', 'paypalindex')->name('paypal.index');
        Route::post('/system/paypal', 'paypalstore')->name('paypal.store');
        Route::post('/profile', 'profileupdate')->name('profile.update');
        Route::post('password', 'passwordupdate')->name('password.update');
        //  Route::post('/pro', 'paypalstore')->name('paypal.store');
    });
    Route::prefix('admin')->controller(CMSController::class)->group(function () {
        Route::get('/cms', 'index')->name('cms.index');
        Route::post('/banner', 'banner')->name('cms.banner');
        Route::post('/firstSections', 'second_section')->name('cms.second_section');
        Route::post('/thirdSections', 'third_section')->name('cms.third_section');
        Route::post('/fourSections', 'four_section')->name('cms.four_section');
        Route::post('/Features', 'features')->name('cms.features');
        Route::post('/footer', 'footer')->name('cms.footer');


    });

    //-------- features---------//
    Route::prefix('features')->controller(FeaturesController::class)->group(function () {
        Route::get('/', 'index')->name('features.index');
        Route::get('/create', 'create')->name('features.create');
        Route::post('/store', 'store')->name('features.store');
        Route::get('/edit/{id}', 'edit')->name('features.edit');
        Route::post('/update/{id}', 'update')->name('features.update');
        Route::delete('/delete/{id}', 'destroy')->name('features.delete');
        Route::get('/status/{id}', 'status')->name('features.status');

    });

    //-------- User---------//
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('user.index');
        Route::get('/create', 'create')->name('user.create');
        Route::post('/store', 'store')->name('user.store');
        Route::get('/edit/{id}', 'edit')->name('user.edit');
        Route::post('/update/{id}', 'update')->name('user.update');
        Route::delete('/delete/{id}', 'destroy')->name('user.delete');
        Route::get('/status/{id}', 'status')->name('user.status');

    });

    
});

require __DIR__.'/auth.php';
// require __DIR__.'/backend_masum.php';
