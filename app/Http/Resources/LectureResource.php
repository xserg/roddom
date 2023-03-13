<?php

namespace App\Http\Resources;

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
    #[OA\Property(property: 'description', description: 'описание лекции', type: 'string')]
    #[OA\Property(property: 'title', description: 'заголовок лекции', type: 'string')]
    #[OA\Property(property: 'preview_picture', description: 'ссылка на превью картинку лекции', type: 'string')]
    #[OA\Property(property: 'is_free', description: 'бесплатная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_recommended', description: 'рекомендованная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_promo', description: 'акционная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_watched', description: 'просмотрена ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_saved', description: 'сохранена ли лекция', type: 'boolean')]
    #[OA\Property(property: 'purchase_info', description: 'куплена ли лекция. Дата до которой куплена. Не важно как покупалась:
    в категории ли, в промо паке или отдельно', type: 'boolean')]
    #[OA\Property(property: 'prices', description: 'цены за лекции, на три периода.
    prices_by_category: цена в рамках подкатегории(в подкатегории цены на все лекции одинаноквые);
    prices_by_promo: цены в рамках акции', type: 'object')]
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
            'lector' => new LectorResource($this->whenLoaded('lector')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'is_free' => $this->is_free,
            'is_recommended' => $this->is_recommended,
            'is_saved' => $this->whenNotNull($this->is_saved),
            'is_promo' => $this->whenNotNull($this->is_promo),
            'is_watched' => $this->whenNotNull($this->is_watched),
            'purchase_info' => $this->whenNotNull($this->purchase_info),
            'prices' => $this->whenNotNull($this->prices),
        ];
    }
}
