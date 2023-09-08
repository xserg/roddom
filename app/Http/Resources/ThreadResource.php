<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Threads\Thread */
class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'last_message' => $this->whenAppended('last_message', $this->messages->last()),
            'messages' => $this->whenAppended('messages', MessageResource::collection($this->messages)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
