<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Models\Threads\Thread;

class CreateThreadController extends Controller
{
    public function __invoke()
    {
        $thread = Thread::create();
        auth()->user()->participants()->updateOrCreate(['thread_id' => $thread->id], ['opened' => true]);

        return response()->json(['id' => $thread->id], 201);
    }
}
