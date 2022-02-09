<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\UsersController;
// use App\Http\Controllers\ProductsController;

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

Route::prefix('v1')->group(function () {

    // Route::apiResource('/user');
    Route::apiResource('/users', 'UsersController');

    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function () {
        Route::post('/login', [AuthController::class, 'logIn']);
        Route::post('/logout', [AuthController::class, 'logOut']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/get-authenticated-user', [AuthController::class, 'getAuthenticatedUser']);
    });
});
