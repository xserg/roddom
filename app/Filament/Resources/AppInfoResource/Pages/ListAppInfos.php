<?php

namespace App\Filament\Resources\AppInfoResource\Pages;

use App\Filament\Resources\AppInfoResource;
use Filament\Resources\Pages\ListRecords;

class ListAppInfos extends ListRecords
{
    protected static string $resource = AppInfoResource::class;

    protected function getActions(): array
    {
        return [
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
