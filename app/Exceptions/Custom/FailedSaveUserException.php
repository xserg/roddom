<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class FailedSaveUserException extends CustomException
{
    protected $message = 'Could not save user to database';
}
