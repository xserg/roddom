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

        Route::delete('/logout', LogoutController::class)
            ->name('login');

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/check', \App\Http\Controllers\Api\User\RetrieveAllController::class)
                ->name('check');

            Route::get('/lectors', \App\Http\Controllers\Api\Lector\RetrieveAllController::class)
                ->name('lectors');

            Route::get('/lectures', \App\Http\Controllers\Api\Lecture\RetrieveAllController::class)
                ->name('lectures');

            Route::get('/diplomas', \App\Http\Controllers\Api\Diploma\RetrieveAllController::class)
                ->name('diplomas');
        });
    });
