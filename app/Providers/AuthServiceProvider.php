<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Threads\Message;
use App\Models\Threads\Thread;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
            return null;
        });

        Gate::define('edit-message', function (User $user, Message $message) {
            return $user->id === $message->thread->user_id;
        });

        Gate::define('show-thread-messages', function (User $user, Thread $thread) {
            return ($user->id === $thread->user_id) && $thread->isOpen();
        });

        Gate::define('close-thread', function (User $user, Thread $thread) {
            return ($user->id === $thread->user_id) && $thread->isOpen();
        });

        Gate::define('add-message-to-thread', function (User $user, Thread $thread) {
            return ($user->id === $thread->user_id) && $thread->isOpen();
        });
    }
}
