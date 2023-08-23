<?php

namespace App\Http\Controllers\Api\CustomNotifications;

use App\Http\Controllers\Controller;

class MarkNotificationReadController extends Controller
{
    public function __invoke()
    {
        auth()->user()->markNotificationRead();

        return response()->json(['message' => 'Set notification read']);
    }
}
