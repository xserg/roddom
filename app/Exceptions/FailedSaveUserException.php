<?php

namespace App\Exceptions;

use Exception;

class FailedSaveUserException extends Exception
{
    protected $message = 'Could not save user to database';
}
