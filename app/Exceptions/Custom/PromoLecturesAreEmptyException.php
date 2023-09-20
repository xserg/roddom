<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class PromoLecturesAreEmptyException extends CustomException
{
    protected $code = 403;
    protected $message = 'There are no promo lectures';
}
