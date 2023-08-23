<?php

namespace App\Http\Controllers\Api\CustomNotifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomNotificationResource;
use App\Models\CustomNotification;

class RetrieveNotificationsController extends Controller
{
    public function __invoke()
    {
        $notification = CustomNotification::latest()->firstOrFail();

        return response()->json(['data' => CustomNotificationResource::make($notification)]);
    }
}
