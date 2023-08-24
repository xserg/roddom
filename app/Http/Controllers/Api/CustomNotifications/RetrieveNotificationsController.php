<?php

namespace App\Http\Controllers\Api\CustomNotifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomNotificationResource;
use App\Models\CustomNotification;
use Illuminate\Http\Request;

class RetrieveNotificationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $latest = $request->query('p');

        if ($latest === 'latest') {
            $latestNotification = CustomNotification::latest()->firstOrFail();

            return response()->json(['data' => CustomNotificationResource::make($latestNotification)]);
        }

        $notifications = CustomNotification::latest()->get();

        return response()->json(['data' => CustomNotificationResource::collection($notifications)]);
    }
}
