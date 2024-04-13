<?php

namespace App\Providers;

use App\Models\Period;
use App\Models\Subscription;
use App\Observers\PeriodObserver;
use App\Observers\SubscriptionObserver;
use App\Repositories\PeriodRepository;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use Illuminate\Contracts\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PeriodRepository::class);
        $this->app->bind(ImageManager::class, function (Application $app) {
            return new ImageManager(['driver' => 'imagick']);
        });
    }

    public function boot(): void
    {
        Subscription::observe(SubscriptionObserver::class);
        Period::observe(PeriodObserver::class);

//        Queue::before(function (JobProcessing $event) {
//            // $event->connectionName
//            Log::info(sprintf('Queue job before handle: %d', $event->job->getJobId()));
//            $jobInstance = unserialize($event->job->payload()['data']['command']);
//
//            Log::info(sprintf('user id is %s', $jobInstance?->user?->id));
//
//            //                 Log::info(sprintf('User\'s id is %d', $event->job->payload()['user']->id));
//            // $event->job->payload()
//        });
//
//        Queue::after(function (JobProcessed $event) {
//            // $event->connectionName
//            Log::info(sprintf('Queue job after handle: %d', $event->job->getJobId()));
//            if ($event->job->hasFailed()) {
//                Log::warning(sprintf('Queue job failed: %d', $event->job->getJobId()));
//            }
//            // $event->job
//            // $event->job->payload()
//        });
//                DB::listen(function ($query) {
//                    $bindings = implode(", ", $query->bindings); // format the bindings as string
//
//                    Log::info("
//                           ------------
//                           Sql: $query->sql
//                           Bindings: $bindings
//                           Time: $query->time
//                           ------------
//                    ");
//                });

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
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
                    ->label('Уведомления и обращения'),
                NavigationGroup::make()
                    ->label('Приложение'),
                NavigationGroup::make()
                    ->label('Партнерская программа'),
                NavigationGroup::make()
                    ->label('Форма «Мой план родов»'),
            ]);
        });
    }
}
