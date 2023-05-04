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
use Illuminate\Support\Arr;

class Lecture extends Model
{
    use HasFactory;

    private $lectureRepository;

    protected $appends = [
        'is_watched',
        'is_promo',
        'is_free',
        'purchase_info',
        'prices',
        'list_watched',
        'id_title',
        'c_type',
        'p_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'show_tariff_1' => 'boolean',
        'show_tariff_2' => 'boolean',
        'show_tariff_3' => 'boolean',
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
            'user_to_saved_lectures',
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

    public function pricesForLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'lectures_prices'
        )->withPivot(['id', 'price']);
    }

    public function pricesInPromoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Promo::class,
            'promo_lectures_prices',
            'lecture_id',
            'promo_id'
        )->withPivot(['price', 'id', 'period_id']);
    }

    public function pricesPeriodsInPromoPacks(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'promo_lectures_prices',
            'lecture_id',
            'period_id'
        )->withPivot(['promo_id', 'price']);
    }

    public function scopeWatched(Builder $query): void
    {
        if (auth()->user()) {
            $watchedIds = auth()
                ->user()
                ->watchedLectures
                ->pluck('id')
                ->toArray();

            $ids = implode(',', $watchedIds);

            $query
                ->whereIn('id', $watchedIds)
                ->orderByRaw("FIELD(id, $ids)");
        }
    }

    public function scopeListWatched(Builder $query): void
    {
        if (auth()->user()) {
            $listWatchedIds = auth()
                ->user()
                ->listWatchedLectures
                ->pluck('id')
                ->toArray();

            $ids = implode(',', $listWatchedIds);

            $query
                ->whereIn('id', $listWatchedIds)
                ->orderByRaw("FIELD(id, $ids)");
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

    public function scopeSaved(Builder $query): void
    {
        if (auth()->user()) {
            $savedIds = auth()
                ->user()
                ->savedLectures
                ->pluck('id')
                ->toArray();

            $ids = implode(',', $savedIds);

            $query
                ->whereIn('id', $savedIds)
                ->orderByRaw("FIELD(id, $ids)");
        }
    }

    public function scopePurchased(Builder $query): void
    {
        $purchasedIds = $this->lectureRepository->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());

        $ids = implode(',', array_keys($purchasedIds));

        $query
            ->whereIn('id', array_keys($purchasedIds))
            ->orderByRaw("FIELD(id, $ids)");
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
            $watchedIds = $user->watchedLectures->pluck('id')->toArray();

            $query->whereNotIn('id', $watchedIds);
        }
    }

    public function scopeRecommended(Builder $query): void
    {
        $query->where('is_recommended', '=', true);
    }

    protected function isPromo(): Attribute
    {
        return new Attribute(
            get: fn() => $this->payment_type_id === LecturePaymentType::PROMO,
        );
    }

    protected function isWatched(): Attribute
    {
        $user = auth()->user();

        if ($user) {
            $watchedLectures = $user->watchedLectures;

            return new Attribute(
                get: fn() => (int)$watchedLectures->contains($this->id),
            );
        }
        return new Attribute(
            get: fn() => 0,
        );
    }

    protected function isSaved(): Attribute
    {
        $user = auth()->user();

        if ($user) {
            $savedLectures = $user->savedLectures;

            return new Attribute(
                get: fn() => (int)$savedLectures->contains($this->id),
            );
        }
        return new Attribute(
            get: fn() => 0,
        );
    }

    protected function listWatched(): Attribute
    {
        $user = auth()->user();

        if (!$user) {
            return new Attribute(
                get: fn() => false,
            );
        }

        $listWatchedLectures = $user->listWatchedLectures;

        return new Attribute(
            get: fn() => (int)$listWatchedLectures
                ->contains($this->id),
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
        $prices = app(LectureRepository::class)->formPricesForLecture($this);

        return new Attribute(
            get: fn() => $prices ?? [],
        );
    }

    protected function rates(): Attribute
    {
        $rates = [];

        $rates['rate_avg'] = LectureRate::query()
            ->where('lecture_id', '=', $this->id)
            ->average('rating');

        if (auth()->user()) {
            $rates['rate_user'] = LectureRate::query()
                ->where('lecture_id', '=', $this->id)
                ->where('user_id', '=', auth()->user()->id)
                ->average('rating');
        } else {
            $rates['rate_user'] = null;
        }

        return new Attribute(
            get: fn() => $rates,
        );
    }

    protected function idTitle(): Attribute
    {
        return new Attribute(
            get: fn() => $this->id . ' ' . $this->title,
        );
    }

    public function setRecommended(): void
    {
        $this->is_recommended = true;
    }

    public function isFree(): Attribute
    {
        return new Attribute(
            get: fn() => $this->payment_type_id === LecturePaymentType::FREE,
        );
    }

    public function cType(): Attribute
    {
        return new Attribute(
            get: fn() => [
                'id' => $this?->contentType?->id,
                'type' => $this?->contentType?->title,
            ],
        );
    }

    public function pType(): Attribute
    {
        return new Attribute(
            get: fn() => [
                'id' => $this?->paymentType?->id,
                'type' => $this?->paymentType?->title,
            ],
        );
    }
}
