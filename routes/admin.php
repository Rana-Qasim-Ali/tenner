<?php 
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BackEnd\AdminController;

/*
|--------------------------------------------------------------------------
| User Interface Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/admin')->middleware(['auth:admin'])->group(function () {
// admin redirect to dashboard route
    Route::get('/dashboard', [AdminController::class,'redirectToDashboard'])->name('admin.dashboard');
    Route::get('/logout', [AdminController::class,'logout'])->name('admin.logout');
});




