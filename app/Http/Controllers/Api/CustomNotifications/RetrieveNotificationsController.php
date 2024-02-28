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
        if ($request->query('p') === 'latest') {
            $latestNotification = CustomNotification::latest()->firstOrFail();

            return CustomNotificationResource::make($latestNotification);
        }

        $notifications = CustomNotification::latest()->get();

        return CustomNotificationResource::collection($notifications);
    }
}
