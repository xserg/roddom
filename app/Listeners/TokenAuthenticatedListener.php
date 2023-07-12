<?php

namespace App\Listeners;

use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Sanctum\Events\TokenAuthenticated;

class TokenAuthenticatedListener
{
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TokenAuthenticated $event): void
    {
        $event->token->tokenable
            ->devices()->updateOrCreate(['device_name' => $event->token->name], ['last_used_at' => now()]);
    }
}
