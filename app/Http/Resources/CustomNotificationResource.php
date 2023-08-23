<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CustomNotification */
class CustomNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'text' => $this->text,
        ];
    }
}
