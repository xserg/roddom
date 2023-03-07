<?php

use App\Http\Controllers\Api\AppInfo\AppInfo;
use App\Http\Controllers\Api\Buy\BuyCategoryController;
use App\Http\Controllers\Api\Buy\BuyLectureController;
use App\Http\Controllers\Api\Buy\BuyPromoController;
use App\Http\Controllers\Api\Category\RetrieveAllCategoriesController;
use App\Http\Controllers\Api\Category\RetrieveCategoryController;
use App\Http\Controllers\Api\Lector\RetrieveAllLectorsController;
use App\Http\Controllers\Api\Lector\RetrieveLectorController;
use App\Http\Controllers\Api\Lecture\RetrieveAllLecturesController;
use App\Http\Controllers\Api\Lecture\RetrieveLectureController;
use App\Http\Controllers\Api\Lecture\SaveLectureController;
use App\Http\Controllers\Api\Lecture\UnsaveLectureController;
use App\Http\Controllers\Api\Lecture\WatchLectureController;
use App\Http\Controllers\Api\ResetPassword\CodeCheckController;
use App\Http\Controllers\Api\ResetPassword\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPassword\ResetPasswordController;
use App\Http\Controllers\Api\User\DeleteUserController;
use App\Http\Controllers\Api\User\LoginController;
use App\Http\Controllers\Api\User\LogoutController;
use App\Http\Controllers\Api\User\PhotoController;
use App\Http\Controllers\Api\User\ProfileRetrieveController;
use App\Http\Controllers\Api\User\ProfileUpdateController;
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
    ->name('v1.')
    ->group(function () {

        Route::get('/app/info', AppInfo::class)
            ->name('app.info');

        Route::post('/user/register', RegisterController::class)
            ->name('register');
        Route::post('/user/login', LoginController::class)
            ->name('login');

        Route::post('password/forgot',  ForgotPasswordController::class);
        Route::post('password/check', CodeCheckController::class);
        Route::post('password/reset', ResetPasswordController::class);
        Route::get('/check', RetrieveAllUsersController::class)
            ->name('check');

        Route::group(['middleware' => ['auth:sanctum']], function () {


            Route::delete('/user', DeleteUserController::class)
                ->name('delete');
            Route::get('/user/profile', ProfileRetrieveController::class)
                ->name('profile.retrieve');
            Route::put('/user/profile', ProfileUpdateController::class)
                ->name('profile.update');
            Route::put('/user/photo', PhotoController::class)
                ->name('photo');
            Route::delete('/user/logout', LogoutController::class)
                ->name('logout');

            Route::get('/lectors', RetrieveAllLectorsController::class)
                ->name('lectors');
            Route::get('/lector/{id}', RetrieveLectorController::class)
                ->name('lector');

            Route::get('/lectures', RetrieveAllLecturesController::class)
                ->name('lectures');
            Route::get('/lecture/{id}', RetrieveLectureController::class)
                ->name('lecture');
            Route::post('/lecture/{id}/watch', WatchLectureController::class)
                ->name('lecture.watch');
            Route::put('/lecture/{id}/save', SaveLectureController::class)
                ->name('lecture.save');
            Route::delete('/lecture/{id}/save', UnsaveLectureController::class)
                ->name('lecture.unsave');
            Route::post('/lecture/{id}/buy/{period}', BuyLectureController::class)
                ->name('lecture.buy')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');

//            Route::get('/diplomas', RetrieveAllDiplomasController::class)
//                ->name('diplomas');

            Route::get('/categories', RetrieveAllCategoriesController::class)
                ->name('categories');
            Route::get('/category/{slug}', RetrieveCategoryController::class)
                ->name('subcategories');
            Route::post('/category/{id}/buy/{period}', BuyCategoryController::class)
                ->name('category.buy')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');


            Route::post('/promopack/buy/{period}', BuyPromoController::class)
                ->name('promopack.buy')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');
        });
    });
