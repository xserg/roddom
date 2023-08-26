<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class FailedCreateResetCodeException extends CustomException
{
    protected $code = 500;
    protected $message = 'Could not create new reset code';
}
