<?php 
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BackEnd\AdminController;
use App\Http\Controllers\BackEnd\Vendor\VendorManagementController;

/*
|--------------------------------------------------------------------------
| User Interface Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/admin')->middleware(['auth:admin'])->group(function () {
// admin redirect to dashboard route
    Route::get('/dashboard', [AdminController::class,'redirectToDashboard'])->name('admin.dashboard');
    Route::get('/logout', [AdminController::class,'logout'])->name('admin.logout');



    // organizer management route start
  Route::prefix('/vendor-management')->group(function () {

    // Route::get('/add-organzer', 'BackEnd\Organizer\OrganizerManagementController@add')->name('admin.organizer_management.add_organizer');
    // Route::post('/save-organzer', 'BackEnd\Organizer\OrganizerManagementController@create')->name('admin.organizer_management.save-organizer');

    Route::get('/registered-vendors',[VendorManagementController::class,'index'])->name('admin.vendor_management.registered_vendor');
    Route::get('/get-vendors', [VendorManagementController::class,'get_vendor'])->name('admin.vendor_management.get_vendor');

    Route::prefix('/organizer/{id}')->group(function () {
    Route::get('/edit', [VendorManagementController::class,'edit'])->name('admin.edit_management.vendor_edit');
    
        // Route::post('/update', 'BackEnd\Vendor\VendorManagementController@update')->name('admin.vendor_management.vendor.update_vendor');
        // Route::post('/update-password', 'BackEnd\Vendor\VendorManagementController@updatePassword')->name('admin.vendor_management.vendor.update_password');
    
        Route::post('/delete', [VendorManagementController::class,'destroy'])->name('admin.vendor_management.vendor.delete');
    //   Route::post('/update-email-status', 'BackEnd\Vendor\VendorManagementController@updateEmailStatus')->name('admin.organizer_management.organizer.update_email_status');

    //   Route::post('/update-account-status', 'BackEnd\Vendor\VendorManagementController@updateAccountStatus')->name('admin.organizer_management.organizer.update_account_status');

    //   Route::get('/details', 'BackEnd\Vendor\VendorManagementController@show')->name('admin.organizer_management.organizer_details');


    //   Route::get('/change-password', 'BackEnd\Vendor\VendorManagementController@changePassword')->name('admin.organizer_management.organizer.change_password');


    //   Route::get('/secret-login', 'BackEnd\Vendor\VendorManagementController@secret_login')->name('admin.organizer_management.organizer.secret_login');
    });
   
  });
  // organizer management route end











});




