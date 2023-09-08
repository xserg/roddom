<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThreadResource;
use App\Models\Threads\Thread;
use Illuminate\Support\Facades\Gate;

class RetrieveThreadController extends Controller
{
    public function __invoke(Thread $thread)
    {
        Gate::authorize('show-thread-messages', $thread);

        $thread->participantForUser(auth()->id())->setReadAtNow();
        $thread = $thread->load(['participants', 'messages.author'])->append('messages');

        return ThreadResource::make($thread);
    }
}
