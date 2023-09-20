<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class TinkoffVerfyIpException extends CustomException
{
    protected $code = 403;
    protected $message = 'Ip is not set as tinkoff whitelisted';
}
