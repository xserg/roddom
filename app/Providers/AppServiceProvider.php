<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        DB::listen(function ($query) {
            // Print it, log it, whatever :)
//             Log::warning($query->sql);
//             Log::warning($query->time);
//             Log::warning('---------------------');
            // $query->bindings
            // $query->time
//        });
    }
}
