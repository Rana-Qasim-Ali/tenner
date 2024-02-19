<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackEnd\Vendor\VendorController;

/*
|--------------------------------------------------------------------------
| User Interface Routes
|--------------------------------------------------------------------------
*/

Route::get('vendor/email/verify', 'BackEnd\Vendor\VendorController@confirm_email');
Route::prefix('/vendor')->group(function () {
  Route::middleware('guest:vendor')->group(function () {
    Route::get('/login', [VendorController::class,'login'])->name('vendor.login');
    Route::get('/signup', [VendorController::class,'signup'])->name('vendor.signup');
    Route::post('/create', [VendorController::class,'create'])->name('vendor.create');
    Route::post('/store', [VendorController::class,'authentication'])->name('vendor.authentication');
    // Route::get('/forget-password', [VendorController::'forget_passord'])->name('vendor.forget.password');
    // Route::post('/send-forget-mail', [VendorController::'forget_mail'])->name('vendor.forget.mail');
    // Route::get('/reset-password', [VendorController::'reset_password'])->name('vendor.reset.password');
    // Route::post('/update-forget-password', [VendorController::'update_password'])->name('vendor.update-forget-password');
  });

  Route::get('/logout', [VendorController::class,'logout'])->name('vendor.logout');
  Route::get('/change-password', [VendorController::class,'change_password'])->name('vendor.change.password');
  Route::post('/update-password', [VendorController::class,'updated_password'])->name('vendor.update_password');
});

Route::prefix('/vendor')->middleware('auth:vendor')->group(function () {
  Route::get('/dashboard', [VendorController::class,'index'])->name('vendor.dashboard');
});
