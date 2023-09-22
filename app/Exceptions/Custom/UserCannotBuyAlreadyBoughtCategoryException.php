<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotBuyAlreadyBoughtCategoryException extends CustomException
{
    protected $code = 403;
    protected $message = 'User cannot buy category that already bought';
}
