<?php

namespace App\Enums;

enum RefTypeEnum: string
{
    case VERTICAL = 'vertical';
    case HORIZONTAL = 'horizontal';

    public function isHorizontal(): bool
    {
        return $this === self::HORIZONTAL;
    }

    public function isVertical(): bool
    {
        return $this === self::VERTICAL;
    }
}
