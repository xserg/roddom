<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResource',
    title: 'MessageResource',
    description: 'Объект сообщения в обращении'
)]
/** @mixin \App\Models\Threads\Message */
class MessageResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'Id сообщения', type: 'integer')]
    #[OA\Property(property: 'message', description: 'Текст сообщения', type: 'string')]
    #[OA\Property(property: 'author', description: 'Автор', type: 'object')]
    #[OA\Property(property: 'author.id', description: 'Айди юзера - автора', type: 'string')]
    #[OA\Property(property: 'author.name', description: 'Имя если имеется либо емэйл юзера - автора', type: 'string')]
    #[OA\Property(property: 'created_at', description: 'Когда создано сообщение', type: 'string')]
    #[OA\Property(property: 'updated_at', description: 'Когда обновлено сообщение', type: 'string')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'author' => [
                'id' => $this->author_id,
                'name' => $this->author->getName()
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
