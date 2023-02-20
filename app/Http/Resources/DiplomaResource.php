<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DiplomaResource',
    title: 'DiplomaResource'
)]
class DiplomaResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id диплома', type: 'integer')]
    #[OA\Property(property: 'preview_picture', description: 'ссылка на превью картинку диплома', type: 'string')]
    #[OA\Property(property: 'lector_id', description: 'id лектора', type: 'integer')]

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'preview_picture' => $this->preview_picture,
            'lector_id' => $this->lector_id
        ];
    }
}
