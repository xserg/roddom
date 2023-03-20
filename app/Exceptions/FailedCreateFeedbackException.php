<?php

namespace App\Exceptions;

use Exception;

class FailedCreateFeedbackException extends Exception
{
    protected $message = 'Could not create feedback';
}
