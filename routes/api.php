<?php

use App\Http\Controllers\Api\AppInfo\AppInfo;
use App\Http\Controllers\Api\Buy\BuyCategoryController;
use App\Http\Controllers\Api\Buy\BuyAllLecturesController;
use App\Http\Controllers\Api\Buy\BuyLectureController;
use App\Http\Controllers\Api\Buy\BuyPromoController;
use App\Http\Controllers\Api\Category\RetrieveAllCategoriesController;
use App\Http\Controllers\Api\Category\RetrieveCategoryController;
use App\Http\Controllers\Api\Lector\RateLectorController;
use App\Http\Controllers\Api\Lector\RetrieveAllLectorsController;
use App\Http\Controllers\Api\Lector\RetrieveLectorController;
use App\Http\Controllers\Api\Lector\RetrieveLectorsByCategoryController;
use App\Http\Controllers\Api\Lecture\AddToListWatchedLectureController;
use App\Http\Controllers\Api\Lecture\AllLecturesPricesController;
use App\Http\Controllers\Api\Lecture\FeedbackLectureController;
use App\Http\Controllers\Api\Lecture\RemoveFromListWatchedLectureController;
use App\Http\Controllers\Api\Lecture\RetrieveAllLecturesController;
use App\Http\Controllers\Api\Lecture\RetrieveLectureController;
use App\Http\Controllers\Api\Lecture\SaveLectureController;
use App\Http\Controllers\Api\Lecture\UnsaveLectureController;
use App\Http\Controllers\Api\Lecture\WatchLectureController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Promo\RetrieveAllPromoLecturesController;
use App\Http\Controllers\Api\ResetPassword\CodeCheckController;
use App\Http\Controllers\Api\ResetPassword\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPassword\ResetPasswordController;
use App\Http\Controllers\Api\User\DeleteUserController;
use App\Http\Controllers\Api\User\LoginCodeController;
use App\Http\Controllers\Api\User\LoginController;
use App\Http\Controllers\Api\User\LogoutController;
use App\Http\Controllers\Api\User\PhotoController;
use App\Http\Controllers\Api\User\PhotoDeleteController;
use App\Http\Controllers\Api\User\ProfileRetrieveController;
use App\Http\Controllers\Api\User\ProfileUpdateController;
use App\Http\Controllers\Api\User\RegisterController;
use App\Http\Controllers\Api\User\ResendLoginCodeController;
use App\Http\Controllers\Api\Wizard\WizardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('v1.')
    ->group(function () {

        Route::get('/app/info', AppInfo::class)
            ->name('app.info');

        Route::post('/user/register', RegisterController::class)
            ->name('register');
        Route::post('/user/login', LoginController::class)
            ->name('login');
        Route::post('/user/login/code', LoginCodeController::class)
            ->name('login.code')
            ->middleware(['throttle:3,1']);
        Route::post('/user/resend-login-code', ResendLoginCodeController::class)
            ->name('resend.login.code')
            ->middleware(['throttle:3,1']);

        Route::post('password/forgot', ForgotPasswordController::class);
        Route::post('password/check', CodeCheckController::class);
        Route::post('password/reset', ResetPasswordController::class);

        //payments notifications listener
        Route::post('/listen', PaymentController::class)
            ->name('payments.listener');

        Route::group(['middleware' => ['auth:sanctum']], function () {

            Route::delete('/user', DeleteUserController::class)
                ->name('delete');
            Route::get('/user/profile', ProfileRetrieveController::class)
                ->name('profile.retrieve');
            Route::put('/user/profile', ProfileUpdateController::class)
                ->name('profile.update');
            Route::put('/user/photo', PhotoController::class)
                ->name('photo');
            Route::delete('/user/photo', PhotoDeleteController::class)
                ->name('photo.delete');
            Route::delete('/user/logout', LogoutController::class)
                ->name('logout');

            Route::get('/lectors', RetrieveAllLectorsController::class)
                ->name('lectors');
            Route::get('/lector/{id}', RetrieveLectorController::class)
                ->name('lector');
            Route::post('/lector/{id}/rate', RateLectorController::class)
                ->name('lector.rate');
            Route::get('/lectors/category/{slug}', RetrieveLectorsByCategoryController::class)
                ->name('lector.by.category');

            Route::get('/lectures', RetrieveAllLecturesController::class)
                ->name('lectures');
            Route::get('/lecture/{id}', RetrieveLectureController::class)
                ->name('lecture');
            Route::post('/lecture/{id}/rate', \App\Http\Controllers\Api\Lecture\RateLectureController::class)
                ->name('lecture.rate');
            Route::post('/lecture/{id}/watch', WatchLectureController::class)
                ->name('lecture.watch');
            Route::post('/lecture/{id}/feedback', FeedbackLectureController::class)
                ->name('lecture.feedback');

            Route::put('/lecture/{id}/save', SaveLectureController::class)
                ->name('lecture.save');
            Route::delete('/lecture/{id}/save', UnsaveLectureController::class)
                ->name('lecture.unsave');

            Route::put('/lecture/{id}/list-watch', AddToListWatchedLectureController::class)
                ->name('lecture.list-watch');
            Route::delete('/lecture/{id}/list-watch', RemoveFromListWatchedLectureController::class)
                ->name('lecture.list-unwatch');

            Route::post('/lecture/{id}/buy/{period}', BuyLectureController::class)
                ->name('lecture.buy')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');

            Route::post('/lecture/all/buy/{period}', BuyAllLecturesController::class)
                ->name('lecture.buy.all')
                ->where('period', '[0-9]+');

            Route::get('/lecture/all/prices', AllLecturesPricesController::class)
                ->name('lecture.all.price');

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
                ->where('period', '[0-9]+');


            Route::get('/promopack', RetrieveAllPromoLecturesController::class)
                ->name('promopack');

            Route::get('/pregnancy-plan/form', WizardController::class);
        });
    });

