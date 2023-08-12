<?php

namespace App\Models;

use App\Models\Scopes\PublishedScope;
use App\QueryBuilders\LectureQueryBuilder;
use App\Traits\MoneyConversion;
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

    protected static function booted(): void
    {
        static::addGlobalScope(new PublishedScope);
    }

    public function newEloquentBuilder($query): LectureQueryBuilder
    {
        return new LectureQueryBuilder($query);
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
        return $this->belongsToMany(User::class, 'user_to_watched_lectures')->distinct();
    }

    public function listWatchedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_list_watched_lectures',
        );
    }

    public function savedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_saved_lectures',
        )->withTimestamps();
    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriptionable');
    }

    public function subscriptionItems(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'subscription_items');
    }

    public function actualSubscriptionItemsForCurrentUser(): BelongsToMany
    {
        return $this->subscriptionItems()
            ->where('user_id', auth()->id())
            ->where('end_date', '>', now());
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

    protected function isWatched(): Attribute
    {
        return new Attribute(
            get: fn () => auth()->user() ?
                $this->watchedUsers->contains('id', auth()->id())
                : false
        );
    }

    protected function isSaved(): Attribute
    {
        return new Attribute(
            get: fn () => auth()->user() ?
                $this->savedUsers->contains('id', auth()->id())
                : false
        );
    }

    protected function listWatched(): Attribute
    {
        return new Attribute(
            get: fn () => auth()->user() ?
                $this->listWatchedUsers->contains('id', auth()->id())
                : false
        );
    }

    public function userRate(): HasOne
    {
        return $this->hasOne(LectureRate::class)->where('user_id', auth()->id());
    }

    public function averageRate(): HasOne
    {
        return $this->hasOne(LectureAverageRate::class);
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
