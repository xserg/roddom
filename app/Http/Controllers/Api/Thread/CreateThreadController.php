<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Models\Threads\Thread;

class CreateThreadController extends Controller
{
    public function __invoke()
    {
        return response()->json(['id' => Thread::create(['user_id' => auth()->id()])->id], 201);
    }
}
