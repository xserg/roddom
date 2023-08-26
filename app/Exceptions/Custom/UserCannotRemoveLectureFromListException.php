<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class UserCannotRemoveLectureFromListException extends CustomException
{
    protected $code = 403;
    protected $message = 'User cannot remove lecture from list';
}
