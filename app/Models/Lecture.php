<?php

namespace App\Models;

use App\Repositories\LectureRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Lecture extends Model
{
    use HasFactory;

    protected $appends = ['is_watched', 'is_promo', 'is_purchased'];
    private $lectureRepository;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->lectureRepository = app(LectureRepository::class);
    }

    protected $hidden = ['pivot'];

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

    public function scopeWatched(Builder $query): void
    {
        $watchedIds = auth()
            ->user()
            ->watchedLectures
            ->pluck('id')
            ->toArray();

        $query->whereIn('id', $watchedIds);
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
        $savedIds = auth()
            ->user()
            ->savedLectures
            ->pluck('id')
            ->toArray();

        $query->whereIn('id', $savedIds);
    }

    public function scopePurchased(Builder $query): void
    {
        $purchasedIds = $this->lectureRepository->getAllPurchasedLectureIdsByUser(auth()->user());

        $query->whereIn('id', $purchasedIds);
    }

    public function scopeFree(Builder $query): void
    {
        $query->where('is_free', '=', 1);
    }

    public function scopePayed(Builder $query): void
    {
        $query->where('is_free', '=', 0);
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

    protected function isPurchased(): Attribute
    {
        $lectureRepository = app(LectureRepository::class);
        $purchasedLecturesIds = $lectureRepository->getAllPurchasedLectureIdsByUser(auth()->user());
        if ($purchasedLecturesIds) {
            return new Attribute(
                get: fn() => (int)in_array($this->id, $purchasedLecturesIds),
            );
        }
        return new Attribute(
            get: fn() => 0,
        );
    }
}
