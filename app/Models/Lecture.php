<?php

namespace App\Models;

use App\Models\Scopes\PublishedScope;
use App\Repositories\LectureRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lecture extends Model
{
    use HasFactory;

    private $lectureRepository;

    protected $appends = [
        'is_watched',
        'is_promo',
        'is_free',
        'prices',
        'list_watched',
        'id_title',
        'a_rates',
    ];

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
        return $this->morphMany(Subscription::class, 'subscriptionable');
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

    public function rates(): HasMany
    {
        return $this->hasMany(LectureRate::class);
    }

    public function scopeWatched(Builder $query): void
    {
        if (auth()->user()) {
            $watchedIds = auth()
                ->user()
                ->watchedLectures
                ->pluck('id')
                ->toArray();

            $ids = implode(',', $watchedIds) ?? '';

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

            $ids = implode(',', $listWatchedIds) ?? '';

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

            $ids = implode(',', $savedIds) ?? '';

            $query
                ->whereIn('id', $savedIds)
                ->orderByRaw("FIELD(id, $ids)");
        }
    }

    public function scopePurchased(Builder $query): void
    {
        $purchasedIds = $this->lectureRepository->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());

        $ids = implode(',', array_keys($purchasedIds)) ?? '';

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
            get: fn () => $this->payment_type_id === LecturePaymentType::PROMO,
        );
    }

    protected function isWatched(): Attribute
    {
        $user = auth()->user();

        if ($user) {
            $watchedLectures = $user->watchedLectures;

            return new Attribute(
                get: fn () => $watchedLectures->contains($this->id),
            );
        }

        return new Attribute(
            get: fn () => false,
        );
    }

    protected function isSaved(): Attribute
    {
        $user = auth()->user();

        if ($user) {
            $savedLectures = $user->savedLectures;

            return new Attribute(
                get: fn () => $savedLectures->contains($this->id),
            );
        }

        return new Attribute(
            get: fn () => false,
        );
    }

    protected function listWatched(): Attribute
    {
        $user = auth()->user();

        if (! $user) {
            return new Attribute(
                get: fn () => false,
            );
        }

        $listWatchedLectures = $user->listWatchedLectures;

        return new Attribute(
            get: fn () => $listWatchedLectures
                ->contains($this->id),
        );
    }

    /**
     * В этот проперти попадают либо промо цены, либо цены категории
     */
    protected function prices(): Attribute
    {
        $prices = $this->lectureRepository->formPricesForLecture($this);

        return new Attribute(
            get: fn () => $prices,
        );
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
                ->where('user_id', '=', auth()->user()->id)
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
            get: fn () => $this->id.' '.$this->title,
        );
    }

    public function setRecommended(): void
    {
        $this->is_recommended = true;
    }

    public function isFree(): Attribute
    {
        return new Attribute(
            get: fn () => $this->payment_type_id === LecturePaymentType::FREE,
        );
    }
}
