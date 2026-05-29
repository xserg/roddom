<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPolicy extends Model
{
    //use HasFactory;

    protected $guarded = [];

    protected $table = 'app_policy';

    public $timestamps = false;
}
