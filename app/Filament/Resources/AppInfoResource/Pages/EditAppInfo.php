<?php

namespace App\Filament\Resources\AppInfoResource\Pages;

use App\Filament\Resources\AppInfoResource;
use Filament\Resources\Pages\EditRecord;

class EditAppInfo extends EditRecord
{
    protected static string $resource = AppInfoResource::class;

    protected function getActions(): array
    {
        return [
        ];
    }
}
