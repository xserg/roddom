<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Models\Threads\Thread;
use Illuminate\Support\Facades\Gate;

class CloseThreadController extends Controller
{
    public function __invoke(Thread $thread)
    {
        Gate::authorize('close-thread', $thread);

        $thread->setStatusClosed();

        return response()->json(status: 204);
    }
}
