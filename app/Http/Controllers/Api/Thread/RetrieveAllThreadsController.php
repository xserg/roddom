<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThreadResource;
use App\Models\Threads\Thread;

class RetrieveAllThreadsController extends Controller
{
    public function __invoke()
    {
        $threads = Thread
            ::whereHas('participants', fn ($query) => $query->where('user_id', auth()->id()))
            ->with(['participants', 'messages.author'])
            ->get()
            ->append('messages');

        return ThreadResource::collection($threads);
    }
}
