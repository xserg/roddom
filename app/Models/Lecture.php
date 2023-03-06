<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lecture extends Model
{
    use HasFactory;

    protected $appends = ['is_promo'];

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

    public function purchasedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_purchased_lectures',
            'lecture_id',
            'user_id'
        );
    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriptions');
    }

    public function promos(): BelongsToMany
    {
        return $this->belongsToMany(
            Promo::class,
            'promo_lectures_prices',
            'lecture_id',
            'promo_id'
        );
    }

    public function scopeWatched(Builder $query): void
    {
        $watchedIds = auth()->user()->watchedLectures->pluck('id')->toArray();

        $query->whereIn('id', $watchedIds);
    }

//    public function scopePurchased(Builder $query): void
//    {
//        $purchasedIds = auth()->user()->purchasedLectures->pluck('id')->toArray();
//
//        $query->whereIn('id', $purchasedIds);
//    }

    public function scopeSaved(Builder $query): void
    {
        $savedIds = auth()->user()->savedLectures->pluck('id')->toArray();

        $query->whereIn('id', $savedIds);
    }

    public function promoPrices(): array
    {
        return [];
    }

    public function setPromoted()
    {
        $this->is_promot = 1;
    }

}
