<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Для того чтобы закидывать в таблицу Subscriptions: subscriptionable - \App\Models\EverythingPack, id - 1.
 * Morph
 */
class EverythingPack extends Model
{
    protected $table = 'everything_pack';
    public $timestamps = false;

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(
            Subscription::class,
            'subscriptions'
        );
    }
}
