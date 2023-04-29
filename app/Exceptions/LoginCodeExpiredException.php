<?php

namespace App\Exceptions;

use Exception;

class LoginCodeExpiredException extends Exception
{
    protected $message = 'Срок действия кода истёк';
}
