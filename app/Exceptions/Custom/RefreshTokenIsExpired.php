<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenIsExpired extends CustomException
{
    protected $code = Response::HTTP_UNAUTHORIZED;
    protected $message = 'Given refresh code is expired';
}
