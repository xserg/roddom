<?php

namespace App\Exceptions;

use Exception;

class UserCannotWatchPaidLectureException extends Exception
{
    protected $message = 'Пользователь не может смотреть платную лекцию';
}
