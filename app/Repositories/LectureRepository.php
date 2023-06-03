<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\LecturePaymentType;
use App\Models\Period;
use App\Models\Promo;
use App\Models\User;
use App\Traits\MoneyConversion;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LectureRepository
{
    use MoneyConversion;

    private $periods;
    private $promoCommonPrices;

    public function __construct(
        private CategoryRepository $categoryRepository,
        private PromoRepository    $promoRepository
    ) {
        $this->periods = Period::all();

        $this->promoCommonPrices = Promo::query()
            ->with(['subscriptionPeriodsForPromoPack'])
            ->first()
            ->subscriptionPeriodsForPromoPack;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getLectureById($id): ?Lecture
    {
        $lecture = Lecture::query()
            ->with('lector', 'lector.diplomas')
            ->where(['id' => $id])
            ->first();

        if (! $lecture) {
            throw new NotFoundHttpException('Лекция c id ' . $id . ' не найдена');
        }

        return $lecture;
    }

    public function getLectureByIdQuery($id, $relations = []): Builder
    {
        $query = Lecture::query()
            ->with($relations)
            ->where(['id' => $id]);

        return $query;
    }

    public function getAllQueryWith($relations = []): Builder
    {
        $builder = Lecture::query()->with($relations);

        return $builder;
    }

    public function getAllPromoQueryWith($relations = []): Builder
    {
        $builder = Lecture::query()->promo()->with($relations);

        return $builder;
    }

    public function addFiltersToQuery(
        Builder|QueryBuilder $builder,
    ): Builder|QueryBuilder {
        $builder = QueryBuilder::for($builder)
            ->allowedSorts(['created_at'])
            ->allowedIncludes(['category', 'lector', 'lector.diplomas'])
            ->allowedFilters([
                AllowedFilter::scope('watched'),
                AllowedFilter::scope('list-watched'),
                AllowedFilter::scope('saved'),
                AllowedFilter::scope('purchased'),
                AllowedFilter::scope('recommended'),
                AllowedFilter::scope('not_watched'),
                AllowedFilter::exact('lector_id'),
                AllowedFilter::exact('category_id'),
            ]);

        return $builder;
    }

    public function paginate(
        QueryBuilder|Builder|Collection $builder,
        ?int                            $perPage,
        ?int                            $page,
    ): LengthAwarePaginator {
        $lectures = $builder
            ->paginate(
                perPage: $perPage,
                page: $page
            )->withQueryString();

        if ($lectures->isEmpty()) {
            throw new NotFoundHttpException(
                'Not found any lecture with such parameters'
            );
        }

        return $lectures;
    }

    /**
     * это надо хранить в бд
     * сейчас истина - subscriptions в бд и цены в аксесорах каждой лекции
     *
     * Формирует массив типа
     *
     *  $lectures = [
     *      24 => [
     *           "start_date" = "2023-05-05 11:51:40",
     *           "end_date" = "2023-05-08 12:51:40"
     * ],
     *      44 => [
     *           "start_date" = "2023-05-05 11:51:40",
     *           "end_date" = "2023-05-08 12:51:40"
     * ],
     *      106 => [
     *           "start_date" = "2023-05-05 11:51:40"
     *           "end_date" = "2023-05-08 12:51:40"
     * ]
     */
    public function getAllPurchasedLecturesIdsAndTheirDatesByUser(
        ?User $user
    ): array {
        $lectures = [];

        if (is_null($user)) {
            return $lectures;
        }

        $lecturesSubscriptions = $user
            ->lectureSubscriptions;

        $categorySubscriptions = $user
            ->categorySubscriptions;

        $promoSubscriptions = $user
            ->promoSubscriptions;

        if ($lecturesSubscriptions && $lecturesSubscriptions->isNotEmpty()) {
            foreach ($lecturesSubscriptions as $lecturesSubscription) {
                if ($lecturesSubscription['end_date'] < now()) {
                    continue;
                }

                $lectures[$lecturesSubscription['subscriptionable_id']] = [
                    'start_date' => $lecturesSubscription['start_date'],
                    'end_date' => $lecturesSubscription['end_date'],
                ];
            }
        }

        if ($categorySubscriptions && $categorySubscriptions->isNotEmpty()) {
            foreach ($categorySubscriptions as $categorySubscription) {
                if ($categorySubscription['end_date'] < now()) {
                    continue;
                }

                $category = $this->categoryRepository->getCategoryById($categorySubscription['subscriptionable_id']);
                if (is_null($category)) {
                    continue;
                }
                $categoryLectures = $category->lectures;
                foreach ($categoryLectures as $lecture) {
                    $lectures[$lecture->id] = [
                        'start_date' => $categorySubscription['start_date'],
                        'end_date' => $categorySubscription['end_date'],
                    ];
                }
            }
        }

        $promoLectures = Lecture::promo()->get();

        if ($promoSubscriptions && $promoSubscriptions->isNotEmpty()) {
            foreach ($promoSubscriptions as $promoSubscription) {
                if ($promoSubscription['end_date'] < now()) {
                    continue;
                }

                //                $promo = $this->promoRepository->getById($promoSubscription['subscriptionable_id']);
                foreach ($promoLectures as $lecture) {
                    $lectures[$lecture->id] = [
                        'start_date' => $promoSubscription['start_date'],
                        'end_date' => $promoSubscription['end_date'],
                    ];
                }
            }
        }

        return $lectures;
    }

    public function setPurchaseInfoToLectures(
        Collection $lectures
    ): Collection {
        $purchasedLectures = $this->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());

        $lectures = $lectures->map(function ($lecture) use ($purchasedLectures) {
            /**
             * @var $lecture Lecture
             */
            $isPurchased = array_key_exists($lecture->id, $purchasedLectures);

            $purchaseInfo = [
                'is_purchased' => array_key_exists($lecture->id, $purchasedLectures),
                'end_date' => $isPurchased ? $purchasedLectures[$lecture->id]['end_date'] : null,
            ];

            $lecture->purchase_info = $purchaseInfo;

            return $lecture;
        });

        return $lectures;
    }

    public function getPurchasedLecturesByUser(Authenticatable|User $user): Collection
    {
        $purchasedLectureIds = $this->getAllPurchasedLecturesIdsAndTheirDatesByUser($user);

        return Lecture::whereIn('id', array_keys($purchasedLectureIds))->get();
    }

    public function getLecturePrice(Lecture $lecture, int $period): int|float
    {
        // аксесор лекции
        $prices = $lecture->prices;
        $priceArr = Arr::where(
            $prices,
            fn ($value) => $value['length'] == $period
        );
        $priceArr = Arr::first($priceArr);

        $price =
            $priceArr['custom_price_for_one_lecture'] ??
            $priceArr['common_price_for_one_lecture'];

        return $price;
    }

    public function formPricesForPromoLecture(Lecture $lecture): array
    {
        $prices = [];

        $promoCustomPrices = $lecture->pricesPeriodsInPromoPacks;
        $promoCommonPrices = $this->promoCommonPrices;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее тут всегда будет указана цена - в priceCommon
            $priceCustom = $promoCustomPrices->where('length', $period->length)->first();
            $priceCommon = $promoCommonPrices->where('length', $period->length)->first();
            $priceCommonForOneLecture = number_format($priceCommon->pivot->price_for_one_lecture / 100, 2, thousands_separator: '');

            //а вот кастомной цены может не быть, поэтому проверяем
            if (is_null($priceCustom)) {
                //если нет кастомной цены для конкретного периода, ставим null
                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => null,
                    'common_price_for_one_lecture' => $priceCommonForOneLecture,
                ];
            } else {
                //а если есть, то преобразовываем в формат рубли.копейки и ставим
                //а common_price_for_one_lecture одна и таже в обоих случаях

                $priceForOneLecture = number_format($priceCustom->pivot->price / 100, 2, thousands_separator: '');

                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => $priceForOneLecture,
                    'common_price_for_one_lecture' => $priceCommonForOneLecture,
                ];
            }
        }

        return $prices;
    }

    public function formPricesForPayedLecture(Lecture $lecture): array
    {
        $prices = [];

        //если не промо - то не важно, платная или бесплатная,
        //бесплатную тоже можно купить по ценам платной
        //берем общую цену за одну лекцию у категории
        $commonCategoryPrices = $lecture->category->categoryPrices;
        $customPrices = $lecture->pricesForLectures;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее тут всегда будет указана цена в priceCommon
            $priceCommon = $commonCategoryPrices->where('period_id', $period->id)->first();
            $priceCustom = $customPrices->where('length', $period->length)->first();

            if (is_null($priceCustom)) {
                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => null,
                    'common_price_for_one_lecture' => self::coinsToRoubles($priceCommon->price_for_one_lecture),
                ];
            } else {
                $priceForOneLecture = self::coinsToRoubles($priceCustom->pivot->price);

                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => $priceForOneLecture,
                    'common_price_for_one_lecture' => self::coinsToRoubles($priceCommon->price_for_one_lecture),
                ];
            }
        }

        return $prices;
    }
}
