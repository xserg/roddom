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
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'preview_picture' => env('APP_URL') . '/' . $this->preview_picture,
        ];
    }
}
