<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class YookassaVerfyIpException extends CustomException
{
    protected $code = 403;
    protected $message = 'Ip is not set as yookassa whitelisted';
}
