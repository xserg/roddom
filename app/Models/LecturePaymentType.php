<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturePaymentType extends Model
{
    const FREE = 1;
    const PAY = 2;
    const PROMO = 3;

    protected $table = 'lecture_payment_types';

}
