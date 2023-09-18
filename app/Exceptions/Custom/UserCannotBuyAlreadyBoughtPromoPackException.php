<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotBuyAlreadyBoughtPromoPackException extends CustomException
{
    protected $code = 403;
    protected $message = 'User cannot buy promo pack that already bought';
}
