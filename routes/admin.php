<?php 
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Interface Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/admin')->middleware(['auth:admin'])->group(function () {
// admin redirect to dashboard route
    Route::get('/dashboard', 'BackEnd\AdminController@redirectToDashboard')->name('admin.dashboard');
    Route::get('/logout', 'BackEnd\AdminController@logout')->name('admin.logout');
});




