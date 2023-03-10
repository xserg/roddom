<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LectorResource',
    title: 'LectorResource'
)]
class LectorResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id лектора', type: 'integer')]
    #[OA\Property(property: 'name', description: 'имя лектора', type: 'string')]
    #[OA\Property(property: 'career_start', description: 'дата начала карьеры', type: 'date')]
    #[OA\Property(property: 'photo', description: 'ссылка на фото лектора', type: 'string')]
    #[OA\Property(
        property: 'diplomas',
        description: 'Дипломы и сертификаты лектора',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/DiplomaResource'),
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'description' => $this->description,
            'career_start' => $this->career_start,
            'photo' => $this->photo,
            'diplomas' => DiplomaCollection::make($this->whenLoaded('diplomas'))
        ];
    }
}
