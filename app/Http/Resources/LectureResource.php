<?php

namespace App\Http\Resources;

use App\Models\Lecture;
use App\Services\CategoryService;
use App\Services\LectureService;
use App\Traits\MoneyConversion;
use Closure;
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
    #[OA\Property(property: 'category_id', description: 'id категории лекции', type: 'integer')]
    #[OA\Property(property: 'description', description: 'описание лекции', type: 'string')]
    #[OA\Property(property: 'title', description: 'заголовок лекции', type: 'string')]
    #[OA\Property(property: 'preview_picture', description: 'ссылка на превью картинку лекции', type: 'string')]
    #[OA\Property(property: 'is_free', description: 'бесплатная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_recommended', description: 'рекомендованная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_promo', description: 'акционная ли лекция', type: 'boolean')]
    #[OA\Property(property: 'is_watched', description: 'просмотрена ли лекция', type: 'boolean')]
    #[OA\Property(property: 'list_watched', description: 'находится ли лекция в списке просмотренных', type: 'string')]
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
    #[OA\Property(property: 'rates', example: [
        'rate_user' => 5,
        'rate_avg' => '3.2525',
    ])]
    public function toArray(Request $request): array
    {
        $loadCategory = $this->resource->relationLoaded('category');
        /**
         * @var Lecture|LectureResource $this
         */
        return [
            'id' => $this->id,
            'lector_id' => $this->lector_id,
            'category_id' => $this->category_id,
            'category_slug' => $this->category->slug,
            'parent_category_slug' => $this->category->parentCategory->slug,
            'title' => $this->title,
            'description' => $this->description,
            'preview_picture' => $this->preview_picture,
            'lector' => new LectorResource($this->whenLoaded('lector')),
            $this->mergeWhen($loadCategory, [
                'category' => new CategoryResource($this->category),
            ]),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'is_free' => $this->isFree(),
            'is_recommended' => $this->is_recommended,
            'is_saved' => $this->whenNotNull($this->is_saved),
            'list_watched' => $this->whenNotNull($this->list_watched),
            'is_promo' => $this->isPromo(),
            'is_watched' => $this->whenNotNull($this->is_watched),
            'purchase_info' => $this->whenAppended('purchase_info', $this->setPurchaseInfo()),
            'prices' => $this->whenAppended('prices', $this->setPrices()),
            'rates' => [
                'rate_avg' => $this->averageRate?->rating,
                'rate_user' => $this->userRate?->rating,
            ],
            'content_type' => $this->whenNotNull(new LectureContentTypeResource($this->contentType)),
            'payment_type' => $this->whenNotNull(new LecturePaymentTypeResource($this->paymentType)),
            'show_tariff_1' => $this->whenNotNull($this->show_tariff_1),
            'show_tariff_2' => $this->whenNotNull($this->show_tariff_2),
            'show_tariff_3' => $this->whenNotNull($this->show_tariff_3),
        ];
    }

    private function setPrices(): Closure
    {
        return function () {
            return $this->isPromo()
                ? $this->convertPrices(app(LectureService::class)->getLecturePricesInCasePromoPack($this))
                : $this->convertPrices(app(CategoryService::class)->getLecturePricesInCaseSubCategory($this));
        };
    }

    private function convertPrices(array $prices): array
    {
        foreach ($prices as &$priceForOnePeriod) {
            if (! is_null($priceForOnePeriod['custom_price_for_one_lecture'])) {
                $priceForOnePeriod['custom_price_for_one_lecture'] = self::coinsToRoubles($priceForOnePeriod['custom_price_for_one_lecture']);
            }
            if (! is_null($priceForOnePeriod['common_price_for_one_lecture'])) {
                $priceForOnePeriod['common_price_for_one_lecture'] = self::coinsToRoubles($priceForOnePeriod['common_price_for_one_lecture']);
            }
        }

        return $prices;
    }

    private function setPurchaseInfo(): array
    {
        /**
         * @var Lecture $this
         */
        $isPurchased = false;
        $endDate = null;

        //подписки, актуальные, принадлежащие текущему юзеру
        $subs = $this->actualSubscriptionItemsForCurrentUser;

        foreach ($subs as $sub) {
            if ($sub->lectures?->contains($this->id)) {
                $isPurchased = true;
            }
            if ($isPurchased && ($sub->end_date > $endDate || is_null($endDate))) {
                $endDate = $sub->end_date;
            }
        }

        return [
            'is_purchased' => $isPurchased,
            'end_date' => $endDate,
        ];
    }
}
