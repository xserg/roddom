<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')
    ->name('v1')
    ->group(function () {
        Route::post('/register', RegisterController::class)
            ->name('register');

        Route::post('/login', LoginController::class)
            ->name('login');

        Route::group(['middleware' => ['auth:sanctum']], function () {

        });
    });
