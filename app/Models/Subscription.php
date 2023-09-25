<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Subscription extends Model
{
    protected $casts = ['exclude' => 'array'];
    protected $guarded = [];

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
        $query->where('start_date', '<', now())
            ->where('end_date', '>', now());
    }

    public function isActual(): bool
    {
        return ($this->end_date > now()) && ($this->start_date < now());
    }
}
