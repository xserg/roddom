<?php

use App\Http\Controllers\Api\AppInfo\AppAgreementController;
use App\Http\Controllers\Api\AppInfo\AppInfoController;
use App\Http\Controllers\Api\Buy\BuyCategoryController;
use App\Http\Controllers\Api\Buy\BuyAllLecturesController;
use App\Http\Controllers\Api\Buy\BuyLectureController;
use App\Http\Controllers\Api\Buy\BuyPromoController;
use App\Http\Controllers\Api\Category\RetrieveAllCategoriesController;
use App\Http\Controllers\Api\Category\RetrieveCategoryController;
use App\Http\Controllers\Api\CustomNotifications\MarkNotificationReadController;
use App\Http\Controllers\Api\CustomNotifications\RetrieveNotificationsController;
use App\Http\Controllers\Api\Lector\RateLectorController;
use App\Http\Controllers\Api\Lector\RetrieveAllLectorsController;
use App\Http\Controllers\Api\Lector\RetrieveLectorController;
use App\Http\Controllers\Api\Lector\RetrieveLectorsByCategoryController;
use App\Http\Controllers\Api\Lecture\AddToListWatchedLectureController;
use App\Http\Controllers\Api\Lecture\AllLecturesPricesController;
use App\Http\Controllers\Api\Lecture\FeedbackLectureController;
use App\Http\Controllers\Api\Lecture\RateLectureController;
use App\Http\Controllers\Api\Lecture\RemoveFromListWatchedLectureController;
use App\Http\Controllers\Api\Lecture\RetrieveAllLecturesController;
use App\Http\Controllers\Api\Lecture\RetrieveLectureController;
use App\Http\Controllers\Api\Lecture\SaveLectureController;
use App\Http\Controllers\Api\Lecture\UnsaveLectureController;
use App\Http\Controllers\Api\Lecture\WatchLectureController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Payment\TinkoffPaymentController;
use App\Http\Controllers\Api\Promo\RetrieveAllPromoLecturesController;
use App\Http\Controllers\Api\ResetPassword\CodeCheckController;
use App\Http\Controllers\Api\ResetPassword\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPassword\ResetPasswordController;
use App\Http\Controllers\Api\Thread\CloseThreadController;
use App\Http\Controllers\Api\Thread\CreateThreadController;
use App\Http\Controllers\Api\Thread\RetrieveAllThreadsController;
use App\Http\Controllers\Api\Thread\RetrieveThreadController;
use App\Http\Controllers\Api\Thread\SendMessageThreadController;
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
use App\Http\Controllers\Api\Wizard\WizardControllerRetrieve;
use App\Http\Controllers\Api\Wizard\WizardEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('v1.')
    ->group(function () {

        Route::get('/app/info', AppInfoController::class)
            ->name('app.info');
        Route::get('/app/agreement', AppAgreementController::class)
            ->name('app.agreement');

        // start
        Route::post('/user/register', RegisterController::class)
            ->name('register');
        Route::post('/user/login', LoginController::class)
            ->name('login')
            ->middleware('throttle:1,0.1,login-attempt');
        Route::post('/user/login/code', LoginCodeController::class)
            ->name('login.code')
            ->middleware('throttle:30,1,login-code');
        Route::post('/user/resend-login-code', ResendLoginCodeController::class)
            ->name('resend.login.code')
            ->middleware('throttle:1,1,resend-login-code');

        Route::post('password/forgot', ForgotPasswordController::class);
        Route::post('password/check', CodeCheckController::class);
        Route::post('password/reset', ResetPasswordController::class);
        // finish
        // поставить mw, которое запретит зареганому юзеру кидать запросы на эти эндпоинты

        //payments notifications listener
        Route::post('/listen', PaymentController::class)
//            ->middleware('yookassa-verify-ip')
            ->name('payments.listener');
        Route::post('/t-listen', TinkoffPaymentController::class)
//            ->middleware('tinkoff-verify-ip')
            ->name('tinkoff-payments.listener');

        Route::group(['middleware' => ['auth:sanctum']], function () {

            Route::delete('/user', DeleteUserController::class)
                ->name('delete');
            // запрет для гостя
            Route::get('/user/profile', ProfileRetrieveController::class)
                ->name('profile.retrieve');
            // запрет
            Route::put('/user/profile', ProfileUpdateController::class)
                ->name('profile.update');
            // запрет для гостя
            Route::put('/user/photo', PhotoController::class)
                ->name('photo');
            // запрет для гостя
            Route::delete('/user/photo', PhotoDeleteController::class)
                ->name('photo.delete');
            // запрет для гостя
            Route::delete('/user/logout', LogoutController::class)
                ->name('logout');
            // запрет для гостя

            Route::get('/lectors', RetrieveAllLectorsController::class)
                ->name('lectors');
            // можно, ничего не переписывать
            Route::get('/lector/{id}', RetrieveLectorController::class)
                ->name('lector');
            // можно, ничего не переписывать

            Route::post('/lector/{id}/rate', RateLectorController::class)
                ->name('lector.rate');
            // запрет для гостя
            Route::get('/lectors/category/{slug}', RetrieveLectorsByCategoryController::class)
                ->name('lector.by.category');
            // можно, ничего не переписывать

            Route::get('/lectures', RetrieveAllLecturesController::class)
                ->name('lectures');
            // можно - переписать методы скоупов лекций, сервисов - везде где встречается юзер
            Route::get('/lecture/{id}', RetrieveLectureController::class)
                ->middleware(['throttle:1,0.015'])
                ->name('lecture');
            // можно - образование цен переделать в сервисах придется если юзер - гость

            Route::post('/lecture/{id}/rate', RateLectureController::class)
                ->name('lecture.rate');
            // запрет для гостя
            Route::post('/lecture/{id}/watch', WatchLectureController::class)
                ->name('lecture.watch');
            // запрет для гостя
            Route::post('/lecture/{id}/feedback', FeedbackLectureController::class)
                ->name('lecture.feedback');
            // запрет для гостя

            Route::put('/lecture/{id}/save', SaveLectureController::class)
                ->name('lecture.save');
            // запрет для гостя
            Route::delete('/lecture/{id}/save', UnsaveLectureController::class)
                ->name('lecture.unsave');
            // запрет для гостя

            Route::put('/lecture/{id}/list-watch', AddToListWatchedLectureController::class)
                ->name('lecture.list-watch');
            // запрет для гостя
            Route::delete('/lecture/{id}/list-watch', RemoveFromListWatchedLectureController::class)
                ->name('lecture.list-unwatch');
            // запрет для гостя

            Route::post('/lecture/{id}/buy/{period}', BuyLectureController::class)
                ->name('lecture.buy')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');
            // запрет для гостя
            Route::post('/lecture/{id}/buy/{period}/order', [BuyLectureController::class, 'prepareOrderForTinkoff'])
                ->name('lecture.buy.order')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');
            // запрет для гостя

            Route::post('/lecture/all/buy/{period}', BuyAllLecturesController::class)
                ->name('lecture.buy.all')
                ->where('period', '[0-9]+');
            // запрет для гостя
            Route::post('/lecture/all/buy/{period}/order', [BuyAllLecturesController::class, 'prepareOrderForTinkoff'])
                ->name('lecture.buy.all.order')
                ->where('period', '[0-9]+');
            // запрет для гостя

            Route::get('/lecture/all/prices', AllLecturesPricesController::class)
                ->name('lecture.all.price');
            // можно - образование цен переделать в сервисах придется если юзер - гость

            Route::get('/categories', RetrieveAllCategoriesController::class)
                ->name('categories');
            // можно - образование цен переделать в сервисах придется если юзер - гость
            Route::get('/category/{slug}', RetrieveCategoryController::class)
                ->name('subcategories');
            // можно - образование цен переделать в сервисах придется если юзер - гость
            // вроде переделывать только метод getPurchasedLectures, и прокидывать соответсвующий userId, везде где он используется

            Route::post('/category/{id}/buy/{period}', BuyCategoryController::class)
                ->name('category.buy')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');
            // запрет для гостя
            Route::post('/category/{id}/buy/{period}/order', [BuyCategoryController::class, 'prepareOrderForTinkoff'])
                ->name('category.buy.order')
                ->where('id', '[0-9]+')
                ->where('period', '[0-9]+');
            // запрет для гостя

            Route::post('/promopack/buy/{period}', BuyPromoController::class)
                ->name('promopack.buy')
                ->where('period', '[0-9]+');
            // запрет для гостя
            Route::post('/promopack/buy/{period}/order', [BuyPromoController::class, 'prepareOrderForTinkoff'])
                ->name('promopack.buy.order')
                ->where('period', '[0-9]+');
            // запрет для гостя


            Route::get('/promopack', RetrieveAllPromoLecturesController::class)
                ->name('promopack');
            // пока опустим, тут вроде не используется пересчет цен для юзера

            Route::get('/pregnancy-plan-form', WizardControllerRetrieve::class); // можно для гостя
            Route::post('/pregnancy-plan-form', WizardEmailController::class); // можно для гостя, добавить в тело запроса
            // email, который вручную потребуется вводить, брать будем не от текущего зареганного юзера, а из тела запроса

            Route::get('/notifications', RetrieveNotificationsController::class)
                ->name('notifications.index'); // узнавать хотим ли мы кидать нотификации гостям и как отслеживать, то что они прочитаны
            Route::put('/notifications/read', MarkNotificationReadController::class)
                ->name('notifications.read'); // запрет для гостей, потому что мы меняем значение в записи юзера

            Route::get('/threads/{thread}', RetrieveThreadController::class)
                ->name('threads.exact.retrieve');  // запрет для гостя
            Route::get('/threads', RetrieveAllThreadsController::class)
                ->name('threads.retrieve'); // запрет для гостя
            Route::post('/threads', CreateThreadController::class)
                ->name('threads.create'); // запрет для гостя
            Route::put('/threads/{thread}', SendMessageThreadController::class)
                ->name('threads.send-message'); // запрет для гостя
            Route::delete('/threads/{thread}', CloseThreadController::class)
                ->name('threads.close'); // запрет для гостя
        });
    });

