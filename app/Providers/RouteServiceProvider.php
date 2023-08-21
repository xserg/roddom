<?php

namespace App\Providers;

use App\Http\Cache\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        $this->createRateLimiter('api', $this->limiterOptions());
        $this->createRateLimiter('login-code', $this->limiterOptions(maxAttempts: 30));
        $this->createRateLimiter('login-attempt', $this->limiterOptions('perSeconds', 1, 6));
    }

    private function createRateLimiter(string $name, object $options): void
    {
        RateLimiter::for($name, function (Request $request) use ($options) {
            return match ($options->type) {
                'perMinute' => Limit::perMinute($options->maxAttempts)->by($request->user()?->id ?: $request->ip()),
                'perSeconds' => Limit::perSeconds($options->decay, $options->maxAttempts)->by($request->user()?->id ?: $request->ip())
            };
        });
    }

    private function limiterOptions(string $type = 'perMinute', int $maxAttempts = 60, int $decay = 60): object
    {
        return (object) [
            'type' => $type,
            'maxAttempts' => $maxAttempts,
            'decay' => $decay
        ];
    }
}
