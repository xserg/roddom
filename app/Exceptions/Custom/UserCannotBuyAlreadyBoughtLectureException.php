<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotBuyAlreadyBoughtLectureException extends CustomException
{
    protected $code = 403;
    protected $message = 'User cannot buy lecture that already bought';
}
