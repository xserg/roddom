<?php

namespace App\Models;

use App\Models\Scopes\PublishedScope;
use App\Repositories\LectureRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Lecture extends Model
{
    use HasFactory;

    private $lectureRepository;

    protected $appends = ['is_watched', 'is_promo', 'purchase_info', 'prices'];

    protected $casts = ['created_at' => 'datetime'];

    protected $hidden = ['pivot'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->lectureRepository = app(LectureRepository::class);
    }

    protected static function booted()
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

    public function watchedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_watched_lectures',
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
        );
    }

//    public function purchasedUsers(): BelongsToMany
//    {
//        return $this->belongsToMany(
//            User::class,
//            'user_to_purchased_lectures',
//            'lecture_id',
//            'user_id'
//        );
//    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriptions');
    }

    public function promoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Promo::class,
            'lectures_to_promo',
            'lecture_id',
            'promo_id'
        );
    }

    public function pricesInPromoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Promo::class,
            'promo_lectures_prices',
            'lecture_id',
            'promo_id'
        )->withPivot('period_id', 'price');
    }

    public function pricesPeriodsInPromoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'promo_lectures_prices',
            'lecture_id',
            'period_id'
        )->withPivot('promo_id', 'price');
    }

    public function scopeWatched(Builder $query): void
    {
        if (auth()->user()) {
            $watchedIds = auth()
                ->user()
                ->watchedLectures
                ->pluck('id')
                ->toArray();

            $query->whereIn('id', $watchedIds);
        }
    }

    public function scopePromo(Builder $query): void
    {
        $firstPromoPack = Promo::first();
        $promoIds = $firstPromoPack
            ->promoLectures
            ->pluck('id')
            ->toArray();

        $query->whereIn('id', $promoIds);
    }

    public function scopeSaved(Builder $query): void
    {
        if (auth()->user()) {
            $savedIds = auth()
                ->user()
                ->savedLectures
                ->pluck('id')
                ->toArray();

            $query->whereIn('id', $savedIds);
        }
    }

    public function scopePurchased(Builder $query): void
    {
        $purchasedIds = $this->lectureRepository->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());

        $query->whereIn('id', array_keys($purchasedIds));
    }

    public function scopeFree(Builder $query): void
    {
        $query->where('is_free', '=', 1);
    }

    public function scopePayed(Builder $query): void
    {
        $query->where('is_free', '=', 0);
    }

    public function scopeRecommended(Builder $query): void
    {
        $query->where('is_recommended', '=', true);
    }

    public function promoPrices(): array
    {
        return [];
    }

    protected function isPromo(): Attribute
    {
        $firstPromoPack = Promo::first();
        $promoIds = $firstPromoPack
            ->promoLectures
            ->pluck('id')
            ->toArray();

        if ($promoIds) {
            return new Attribute(
                get: fn() => (int)in_array($this->id, $promoIds),
            );
        }
        return new Attribute(
            get: fn() => 0,
        );
    }

    protected function isWatched(): Attribute
    {
        $user = auth()->user();
        $watchedLectures = $user->watchedLectures;

        if ($watchedLectures) {
            return new Attribute(
                get: fn() => (int)$user->watchedLectures->contains($this->id),
            );
        }
        return new Attribute(
            get: fn() => 0,
        );
    }

    protected function purchaseInfo(): Attribute
    {
        $lectureRepository = app(LectureRepository::class);
        $purchasedLectures = $lectureRepository->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());

        $isPurchased = (int)array_key_exists($this->id, $purchasedLectures);

        $purchasedInfo = [
            'is_purchased' => (int)array_key_exists($this->id, $purchasedLectures),
            'end_date' => $isPurchased == 1 ? $purchasedLectures[$this->id]['end_date'] : null
        ];

        if ($purchasedLectures) {
            return new Attribute(
                get: fn() => $purchasedInfo,
            );
        }
        return new Attribute(
            get: fn() => 0,
        );
    }

    protected function prices(): Attribute
    {
        $prices = $this->category->categoryPrices;
        $result = [];

        foreach ($prices as $price) {
            $priceForLecture = number_format($price->price_for_one_lecture / 100, 2, thousands_separator: '');
            $result['price_by_category'][] = [
                'title' => $price->period->title,
                'length' => $price->period->length,
                'price_for_lecture' => $priceForLecture
            ];
        }

        $periods = $this->pricesPeriodsInPromoPacks;
        if ($periods->isNotEmpty()) {
            foreach ($periods as $period) {
                $priceForLecture = number_format($period->pivot->price / 100, 2, thousands_separator: '');
                $result['price_by_promo'][$period->length] = [
                    'title' => $period->title,
                    'length' => $period->length,
                    'price_for_promo_lecture' => $priceForLecture,
                ];
            }
        }

        if ($result) {
            return new Attribute(
                get: fn() => $result,
            );
        }
        return new Attribute(
            get: fn() => [],
        );
    }

    public function setRecommended(): void
    {
        $this->is_recommended = true;
    }
}
