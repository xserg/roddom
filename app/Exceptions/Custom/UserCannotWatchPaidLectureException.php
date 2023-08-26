<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotWatchPaidLectureException extends CustomException
{
    protected $code = 403;
    protected $message = 'Пользователь не может смотреть платную лекцию';
}
