<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class ResetCodeExpiredException extends CustomException
{
    protected $code = 422;
    protected $message = 'Срок действия кода истёк';
}
