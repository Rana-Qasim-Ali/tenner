<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'API', 'prefix' => 'v1/', 'as' => 'v1.'], function () {
    Route::post('register', [RegisterApiController::class, 'register']);
    Route::post('login', [RegisterApiController::class, 'login']);
    
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', [RegisterApiController::class, 'logout']);
    });
    
});