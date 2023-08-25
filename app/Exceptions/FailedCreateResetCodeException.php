<?php

namespace App\Exceptions;

use Exception;

class FailedCreateResetCodeException extends Exception
{
    protected $message = 'Could not create new reset code';
    protected $code = 500;
}
