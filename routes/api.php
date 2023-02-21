<?php

use App\Http\Controllers\Api\Diploma\RetrieveAllDiplomasController;
use App\Http\Controllers\Api\Lector\RetrieveAllLectorsController;
use App\Http\Controllers\Api\Lector\RetrieveLectorController;
use App\Http\Controllers\Api\Lecture\RetrieveAllLecturesController;
use App\Http\Controllers\Api\Lecture\RetrieveLectureController;
use App\Http\Controllers\Api\User\DeleteUserController;
use App\Http\Controllers\Api\User\LoginController;
use App\Http\Controllers\Api\User\RegisterController;
use App\Http\Controllers\Api\User\RetrieveAllUsersController;
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

        Route::post('/user/register', RegisterController::class)
            ->name('register');

        Route::post('/user/login', LoginController::class)
            ->name('login');

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/check', RetrieveAllUsersController::class)
                ->name('check');

            Route::delete('/user/{id}', DeleteUserController::class)
                ->name('delete');

            Route::get('/lectors', RetrieveAllLectorsController::class)
                ->name('lectors');
            Route::get('/lector/{id}', RetrieveLectorController::class)
                ->name('lector');

            Route::get('/lectures', RetrieveAllLecturesController::class)
                ->name('lectures');
            Route::get('/lecture/{id}', RetrieveLectureController::class)
                ->name('lecture');

            Route::get('/diplomas', RetrieveAllDiplomasController::class)
                ->name('diplomas');
        });
    });
