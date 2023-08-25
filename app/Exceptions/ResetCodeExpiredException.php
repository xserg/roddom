<?php

namespace App\Exceptions;

use Exception;

class ResetCodeExpiredException extends Exception
{
    protected $message = 'Срок действия кода истёк';
    protected $code = 422;
}
