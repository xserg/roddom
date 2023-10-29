<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategoryResource',
    title: 'CategoryResource'
)]
/** @mixin Category */
class CategoryResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id категории', type: 'integer')]
    #[OA\Property(property: 'title', description: 'title категории', type: 'string')]
    #[OA\Property(property: 'parent_id', description: 'id основной категории. 0 - если это уже основная категория', type: 'integer')]
    #[OA\Property(property: 'slug', description: 'slug категории', type: 'string')]
    #[OA\Property(property: 'parent_slug', description: 'slug наименования родительской категории', type: 'string')]
    #[OA\Property(property: 'description', description: 'описание категории', type: 'string')]
    #[OA\Property(property: 'info', description: 'инфо категории', type: 'string')]
    #[OA\Property(property: 'preview_picture', description: 'превью картинка категории', type: 'string')]
    #[OA\Property(property: 'prices', description: 'цены за всю подкатегорию лекций, на три периода', type: 'object')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'parent_id' => $this->parent_id,
            'parent_slug' => $this->whenNotNull($this->parent_slug),
            'slug' => $this->slug,
            'description' => $this->description,
            'info' => $this->info,
            'preview_picture' => $this->preview_picture,
            'is_promo' => $this->is_promo,
            'lectures_count' => $this->isMain() ?
                $this->whenCounted('childrenCategoriesLectures') :
                $this->whenCounted('lectures'),
            'prices' => $this->whenAppended('prices'),
        ];
    }
}
