<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CustomNotificationResource',
    title: 'CustomNotificationResource'
)]
/** @mixin \App\Models\CustomNotification */
class CustomNotificationResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id нотификации', type: 'integer')]
    #[OA\Property(property: 'title', description: 'текст нотификации', type: 'string')]
    #[OA\Property(property: 'date', description: 'created at', type: 'datetime')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'date' => $this->created_at
        ];
    }
}
