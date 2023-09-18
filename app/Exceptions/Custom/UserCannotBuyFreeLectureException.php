<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotBuyFreeLectureException extends CustomException
{
    protected $code = 403;
    protected $message = 'User cannot buy free lecture';
}
