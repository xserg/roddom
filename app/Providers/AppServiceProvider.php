<?php

namespace App\Providers;

use App\Repositories\LectureRepository;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $this->app->singleton(LectureRepository::class, LectureRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //        DB::listen(function ($query) {
        //            $bindings = implode(", ", $query->bindings); // format the bindings as string
        //
        //            Log::info("
        //                   ------------
        //                   Sql: $query->sql
        //                   Bindings: $bindings
        //                   Time: $query->time
        //                   ------------
        //            ");
        //        });

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param  int  $perPage
         * @param  int  $total
         * @param  int  $page
         * @param  string  $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage)->values(),
                $total ?: $this->count(),
                $perPage ?? 15,
                $page ?? 1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                    ->label('Лекции'),
                NavigationGroup::make()
                    ->label('Пользователи'),
                NavigationGroup::make()
                    ->label('Приложение'),
            ]);
        });
    }
}
