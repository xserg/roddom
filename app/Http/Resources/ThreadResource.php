<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ThreadResource',
    title: 'ThreadResource',
    description: 'Объект обращения'
)]
/** @mixin \App\Models\Threads\Thread */
class ThreadResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'Id обращения', type: 'integer')]
    #[OA\Property(property: 'status', description: 'Строка "open" или "closed"', type: 'string')]
    #[OA\Property(property: 'last_message', description: 'Последнее сообщение', type: 'string')]
    #[OA\Property(property: 'messages', description: 'Сообщения этого обращения', type: 'string')]
    #[OA\Property(property: 'created_at', description: 'Когда создано обращение', type: 'string')]
    #[OA\Property(property: 'updated_at', description: 'Когда обновлено обращение', type: 'string')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'last_message' => $this->whenAppended('last_message', MessageResource::make($this->messages->last())),
            'messages' => $this->whenAppended('messages', MessageResource::collection($this->messages)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
