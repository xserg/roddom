<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategoryResource',
    title: 'CategoryResource'
)]

class CategoryResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id категории', type: 'integer')]
    #[OA\Property(property: 'slug', description: 'slug категории', type: 'string')]
    #[OA\Property(property: 'description', description: 'описание категории', type: 'string')]
    #[OA\Property(property: 'info', description: 'инфо категории', type: 'string')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'info' => $this->info,
        ];
    }
}
