<?php

namespace App\Http\Resources;

use App\Models\Promo;
use App\Services\LectureService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LectureResource',
    title: 'LectureResource'
)]
class LectureResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id лекции', type: 'integer')]
    #[OA\Property(property: 'lector_id', description: 'id лектора - автора лекции', type: 'integer')]
    #[OA\Property(property: 'category_id', description: 'id категории лекции', type: 'integer')]
    #[OA\Property(property: 'title', description: 'заголовок лекции', type: 'string')]
    #[OA\Property(property: 'preview_picture', description: 'ссылка на превью картинку лекции', type: 'string')]
//    #[OA\Property(property: 'video_id', description: 'id видео в kinescope.io, https://kinescope.io/{video_id}
//    скорее всего сделаем так: когда у юзера оплачена подписка или лекции бесплатная(is_free) - тогда отдаём фронту это значение,
//    в остальных случаях null', type: 'integer')]
    #[OA\Property(property: 'is_free', description: 'бесплатная ли лекция', type: 'boolean')]
    #[OA\Property(
        property: 'lector',
        ref: '#/components/schemas/LectorResource',
        description: 'Лектор лекции',
        type: 'object')]
    #[OA\Property(
        property: 'category',
        ref: '#/components/schemas/CategoryResource',
        description: 'Категория лекции',
        type: 'object')]
    #[OA\Property(
        property: 'created_at',
        description: 'Дата и время создания',
        type: 'string',
        format: 'datetime')
    ]

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lector_id' => $this->lector_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'preview_picture' => $this->preview_picture,
            'is_free' => $this->is_free,
            'is_promo' => Promo::first()->promoLectures()->contains($this->id),
            'lector' => LectorResource::make($this->whenLoaded('lector')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'created_at' => $this->created_at,
        ];
    }
}
