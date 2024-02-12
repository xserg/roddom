<?php
declare(strict_types=1);

namespace App\Dto\User;

class LoginCodeDto
{
    public function __construct(
        private readonly string  $code,
        private readonly ?string $device_name = null
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDeviceName(): ?string
    {
        return $this->device_name;
    }
}
