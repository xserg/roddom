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
            'status' => $this->status,
            'messages' => MessageResource::collection($this->messages),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
