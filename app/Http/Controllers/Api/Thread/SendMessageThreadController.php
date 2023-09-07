<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Http\Requests\Thread\AddMessageToThreadRequest;
use App\Http\Resources\MessageResource;
use App\Models\Threads\Thread;
use Illuminate\Support\Facades\Gate;

class SendMessageThreadController extends Controller
{
    public function __invoke(AddMessageToThreadRequest $request, Thread $thread)
    {
        Gate::authorize('add-message-to-thread', $thread);

        $messages = $thread->messages()->create([...$request->validated(), 'author_id' => auth()->id()]);

        return MessageResource::collection($thread->messages);
    }
}
