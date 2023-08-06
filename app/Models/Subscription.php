<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'period_id',
        'subscriptionable_type',
        'subscriptionable_id',
        'start_date',
        'end_date',
        'total_price',
        'entity_title',
        'points'
    ];

    protected static function booted()
    {
        static::creating(self::makeEntityTitle());
        static::updating(self::makeEntityTitle());
    }

    private static function makeEntityTitle(): Closure
    {
        return function (Subscription $subscription) {
            if ($subscription->subscriptionable_type == Lecture::class) {
                $entityTitle = 'Лекция: ' . Lecture::query()->find($subscription->subscriptionable_id)->title;
            } elseif ($subscription->subscriptionable_type == Category::class) {
                $entityTitle = 'Категория: ' . Category::query()->find($subscription->subscriptionable_id)->title;
            } elseif ($subscription->subscriptionable_type == Promo::class) {
                $entityTitle = 'Промопак лекций';
            } elseif ($subscription->subscriptionable_type == EverythingPack::class) {
                $entityTitle = 'Все лекции';
            } else {
                $entityTitle = 'Заголовок лекции не определён';
            }

            $subscription->entity_title = $entityTitle;
        };
    }

    public function subscriptionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function lectures(): BelongsToMany
    {
        return $this->belongsToMany(Lecture::class, table: 'subscription_items');
    }

    public function scopeActual(Builder $query): void
    {
        $query->where('end_date', '>', now());
    }

    public function isActual(): bool
    {
        return $this->end_date > now();
    }
}
