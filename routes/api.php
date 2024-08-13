<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'v1',  'namespace' => 'Api'], function(){
    
    Route::group(['prefix' => 'auth'], function() {
        
        Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);
        Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:api');;
	});

    
    Route::group(['prefix' => 'posts', 'middleware' => ['auth:api'] ], function() {
        Route::get('/', [App\Http\Controllers\Api\PostController::class, 'index']);    
    });


    
});