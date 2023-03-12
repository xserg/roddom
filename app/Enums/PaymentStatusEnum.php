<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case CREATED = 'CREATED';
    case CONFIRMED = 'CONFIRMED';
    case FAILED = 'FAILED';
}
