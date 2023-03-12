<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'price',
        'description',
        'user_id',
        'status',
        'subscriptionable_type',
        'subscriptionable_id',
        'period'
    ];
}
