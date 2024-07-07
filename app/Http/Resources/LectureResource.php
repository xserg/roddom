<?php

namespace App\Http\Resources;

use App\Traits\MoneyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LectureResource',
    title: 'LectureResource'
)]
/** @mixin \App\Models\Lecture */
class LectureResource extends JsonResource
{
    use MoneyConversion;

    #[OA\Property(property: 'id', description: 'id лекции', type: 'integer')]
    #[OA\Property(property: 'lector_id', description: 'id лектора - автора лекции', type: 'integer')]
    #[OA\Property(property: 'category_id', description: 'id подкатегории лекции', type: 'integer')]
    #[OA\Property(property: 'parent_category_slug', description: 'slug подкатегории лекции', type: 'string')]
    #[OA\Property(property: 'title', description: 'заголовок лекции', type: 'string')]
    #[OA\Property(property: 'description', description: 'описание лекции', type: 'string')]
    #[OA\Property(property: 'preview_picture', description: 'ссылка на превью картинку лекции.
    Необходимо добавить "api-url/storage/" перед этой строкой', type: 'string')]
    #[OA\Property(property: 'lector', ref: '#/components/schemas/LectorResource', description: 'Лектор лекции',
        type: 'object')]
    #[OA\Property(property: 'category', ref: '#/components/schemas/CategoryResource', description: 'Категория лекции',
        type: 'object')]
    #[OA\Property(property: 'created_at', description: 'Дата и время создания в формате Y-m-d H:i:s', type: 'string', format: 'datetime')]
    #[OA\Property(property: 'is_free', description: 'бесплатная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_recommended', description: 'рекомендованная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_saved', description: 'сохранена ли лекция', type: 'boolean')]
    #[OA\Property(property: 'list_watched', description: 'находится ли лекция в списке просмотренных(тыкал ли на глазик)', type: 'string')]
    #[OA\Property(property: 'is_promo', description: 'акционная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_watched', description: 'просмотрена ли лекция(тыкал ли юзер на кнопку плей лекции)', type: 'boolean')]
    #[OA\Property(property: 'purchase_info', description: 'куплена ли лекция. Дата до которой куплена. Не важно как покупалась:
    в категории ли, в промо паке или отдельно', type: 'object', example: [
        "is_purchased" => true,
        "end_date" => "2024-03-01 00:00:00"
    ])]
    #[OA\Property(property: 'prices', description: 'Массив с тремя объектами, каждый для своего периода. В объектах 4 поля:
        length: длительность периода покупки (int);
        period_id: айди периода (int);
        custom_price_for_one_lecture: если есть, то берется она (str);
        common_price_for_one_lecture: если custom нет, то берется она. Она всегда присутствует. (str)', type: 'object')]
    #[OA\Property(property: 'rates', description: 'объект с двумя полями: rate_user рейтинг от текущего юзера
    и rate_avg средний рейтинг. Максимум 10 и там и там, минимум 1', type: 'object', example: [
        'rate_user' => 5,
        'rate_avg' => '3.2525',
    ])]
    #[OA\Property(property: 'content_type', description: 'Тип лекции, три типа бывает: kinescope/pdf/embed(youtube/rutube)',
        type: 'object', example: [
            'id' => 1,
            'title' => 'kinescope',
        ])]
    #[OA\Property(property: 'payment_type', description: '1 - бесплатная(free), 2 - платная(pay), 3 - промо(promo)',
        type: 'object', example: [
            'id' => 1,
            'title' => 'pay',
        ])]
    public function toArray(Request $request): array
    {
        $loadCategory = $this->resource->relationLoaded('category');

        return [
            'id' => $this->id,
            'lector_id' => $this->lector_id,
            'category_id' => $this->category_id,
            'category_slug' => $this->category->slug,
            'parent_category_slug' => $this->category->parentCategory->slug,
            'title' => $this->title,
            'description' => $this->description,
            'preview_picture' => env('APP_URL') . '/' . $this->preview_picture,
            'lector' => new LectorResource($this->whenLoaded('lector')),
            $this->mergeWhen($loadCategory, [
                'category' => new CategoryResource($this->category),
            ]),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'is_free' => $this->isFree(),
            'is_recommended' => $this->is_recommended,
            'is_saved' => $this->is_saved,
            'list_watched' => $this->list_watched,
            'is_promo' => $this->isPromo(),
            'is_watched' => $this->is_watched,
            'purchase_info' => $this->whenAppended('purchase_info'),
            'prices' => $this->whenAppended('prices'),
            'rates' => [
                'rate_avg' => $this->averageRate?->rating,
                'rate_user' => $this->userRate?->rating,
            ],
            'content_type' => new LectureContentTypeResource($this->whenLoaded('contentType')),
            'payment_type' => new LecturePaymentTypeResource($this->whenLoaded('paymentType')),
            'show_tariff_1' => $this->show_tariff_1,
            'show_tariff_2' => $this->show_tariff_2,
            'show_tariff_3' => $this->show_tariff_3,
        ];
    }
}
