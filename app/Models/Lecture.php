<?php

namespace App\Models;

use App\Models\Scopes\PublishedScope;
use App\Services\LectureService;
use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;

class Lecture extends Model
{
    use HasFactory, MoneyConversion;

    private $lectureService;

//    protected $appends = [
//        'is_watched',
//        'prices',
//        'is_saved',
//        'list_watched',
//        'id_title',
//        'a_rates',
//    ];

    protected $casts = [
        'created_at' => 'datetime',
        'show_tariff_1' => 'boolean',
        'show_tariff_2' => 'boolean',
        'show_tariff_3' => 'boolean',
        'is_recommended' => 'boolean',
    ];

    protected $fillable = [
        'id',
        'lector_id',
        'description',
        'title',
        'preview_picture',
        'category_id',
        'content',
        'is_published',
        'is_recommended',
        'content_type_id',
        'payment_type_id',
        'show_tariff_1',
        'show_tariff_2',
        'show_tariff_3',
    ];

    protected $hidden = ['pivot'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->lectureService = app(LectureService::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new PublishedScope);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }

    public function contentType(): BelongsTo
    {
        return $this->belongsTo(LectureContentType::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(LecturePaymentType::class);
    }

    public function watchedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_watched_lectures',
            'lecture_id',
            'user_id'
        );
    }

    public function listWatchedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_list_watched_lectures',
            'lecture_id',
            'user_id'
        );
    }

    public function savedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_saved_lectures',
            'lecture_id',
            'user_id'
        )->withTimestamps();
    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriptionable');
    }

    /**
     * Кастомные цены на платную лекцию
     */
    public function pricesForLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'lectures_prices'
        )->withPivot(['id', 'price']);
    }

    /**
     * Кастомные цены на промо лекцию
     */
    public function pricesInPromoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Promo::class,
            'promo_lectures_prices',
            'lecture_id',
            'promo_id'
        )->withPivot(['price', 'id', 'period_id']);
    }

    /**
     * Кастомные цены на промо лекцию тоже, только через период
     */
    public function pricesPeriodsInPromoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'promo_lectures_prices',
            'lecture_id',
            'period_id'
        )->withPivot(['promo_id', 'price']);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(LectureRate::class);
    }

    public function scopeWatched(Builder $query): void
    {
        if (auth()->user()) {
            $watchedIds = auth()
                ->user()
                ->watchedLectures()
                ->pluck($this->getTable() . '.' . $this->getKeyName())
                ->toArray();

            $query->whereIn('id', $watchedIds);

            if (! empty($watchedIds)) {
                $ids = implode(',', $watchedIds);
                $query->orderByRaw("FIELD(id, $ids)");
            }
        }
    }

    public function scopeListWatched(Builder $query): void
    {
        if (auth()->user()) {
            $listWatchedIds = auth()
                ->user()
                ->listWatchedLectures()
                ->pluck($this->getTable() . '.' . $this->getKeyName())
                ->toArray();

            $query->whereIn('id', $listWatchedIds);

            if (! empty($listWatchedIds)) {
                $ids = implode(',', $listWatchedIds);
                $query->orderByRaw("FIELD(id, $ids)");
            }
        }
    }

    public function scopeSaved(Builder $query): void
    {
        if (auth()->user()) {
            $savedIds = auth()
                ->user()
                ->savedLectures()
                ->pluck($this->getTable() . '.' . $this->getKeyName())
                ->toArray();

            $query->whereIn('id', $savedIds);

            if (! empty($savedIds)) {
                $ids = implode(',', $savedIds);
                $query->orderByRaw("FIELD(id, $ids)");
            }
        }
    }

    public function scopePromo(Builder $query): void
    {
        $query->where('payment_type_id', LecturePaymentType::PROMO);
    }

    public function scopeNotPromo(Builder $query): void
    {
        $query->where('payment_type_id', '!=', LecturePaymentType::PROMO);
    }

    public function scopePurchased(Builder $query): void
    {
        $purchasedIds = $this->lectureService->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());

        $ids = implode(',', array_keys($purchasedIds)) ?? '';

        $query
            ->whereIn('id', array_keys($purchasedIds));
        //            ->orderByRaw("FIELD(id, $ids)");
    }

    public function scopeFree(Builder $query): void
    {
        $query->where('payment_type_id', '=', LecturePaymentType::FREE);
    }

    public function scopePayed(Builder $query): void
    {
        $query->where('payment_type_id', '!=', LecturePaymentType::FREE);
    }

    public function scopeNotWatched(Builder $query): void
    {
        $user = auth()->user();
        if ($user) {
            $query->whereDoesntHave('watchedUsers', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
        }
    }

    public function scopeRecommended(Builder $query): void
    {
        $query->where('is_recommended', '=', true);
    }

    protected function isWatched(): Attribute
    {
        if (! auth()->user()) {
            return new Attribute(
                get: fn () => false,
            );
        }

        return new Attribute(
            get: fn () => $this
                ->watchedUsers
                ->contains('id', auth()->id())
        );
    }

    protected function isSaved(): Attribute
    {
        if (! auth()->user()) {
            return new Attribute(
                get: fn () => false,
            );
        }

        return new Attribute(
            get: fn () => $this
                ->savedUsers
                ->contains('id', auth()->id())
        );
    }

    protected function listWatched(): Attribute
    {
        if (! auth()->user()) {
            return new Attribute(
                get: fn () => false,
            );
        }

        return new Attribute(
            get: fn () => $this
                ->listWatchedUsers
                ->contains('id', auth()->id())
        );
    }

//    /**
//     * В этот аксессор попадают либо промо цены, либо цены категории - общие и кастомные
//     * на каждый период
//     */
//    protected function prices(): Attribute
//    {
//        if ($this->isPromo()) {
//            $prices = $this->lectureService->formPromoLecturePricesPromoPack($this);
//        } else {
//            $prices = $this->lectureService->formLecturePricesSubCategory($this);
//        }
//
//        return new Attribute(
//            get: fn () => $prices,
//        );
//    }

    public function convertPrices(array $prices): array
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

    protected function aRates(): Attribute
    {
        $rates = [];

        $rates['rate_avg'] = $this
            ->rates
            ->average('rating');

        if (auth()->user()) {
            $rates['rate_user'] = $this
                ->rates
                ->where('user_id', '=', auth()->id())
                ->average('rating');
        } else {
            $rates['rate_user'] = null;
        }

        return new Attribute(
            get: fn () => $rates,
        );
    }

    protected function idTitle(): Attribute
    {
        return new Attribute(
            get: fn () => $this->id . ' ' . $this->title,
        );
    }

    public function isFree(): bool
    {
        return $this->payment_type_id === LecturePaymentType::FREE;
    }

    public function isPromo(): bool
    {
        return $this->payment_type_id === LecturePaymentType::PROMO;
    }

    public function setRecommended(): void
    {
        $this->is_recommended = true;
    }
}
