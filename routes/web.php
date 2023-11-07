<?php

use \App\Http\Controllers\Web\ApiDocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/api-docs/login', [ApiDocumentationController::class, 'showLoginForm'])
    ->name('api-docs.login');

Route::post('/api-docs/login', [ApiDocumentationController::class, 'login'])
    ->name('api-docs.login.verify');

Route::delete('/api-docs/login', [ApiDocumentationController::class, 'logout'])
    ->name('api-docs.logout');

Route::get('/api-docs', [ApiDocumentationController::class, 'docs'])
    ->name('api-docs');
