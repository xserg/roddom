<?php

namespace App\Exceptions;

use Exception;

class FailedCreateLoginCodeException extends Exception
{
    protected $message = 'Could not create new login code';
}
