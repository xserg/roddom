<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LecturesInProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'preview_picture' => $this->preview_picture,
            'is_free' => $this->is_free,
            'is_promo' => $this->whenNotNull($this->is_promo),
            'is_watched' => $this->whenNotNull($this->is_watched),
            'purchase_info' => $this->whenNotNull($this->purchase_info),
        ];
    }
}
