<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class FailedCreateLoginCodeException extends CustomException
{
    protected $message = 'Could not create new login code';
}
