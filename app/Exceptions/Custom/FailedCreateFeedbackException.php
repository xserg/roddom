<?php

namespace App\Exceptions\Custom;

use App\Exceptions\CustomException;

class FailedCreateFeedbackException extends CustomException
{
    protected $message = 'Could not create feedback';
}
