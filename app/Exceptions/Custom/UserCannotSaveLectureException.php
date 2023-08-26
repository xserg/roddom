<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotSaveLectureException extends CustomException
{
    protected $code = 403;
    protected $message = 'User cannot save lecture';
}
