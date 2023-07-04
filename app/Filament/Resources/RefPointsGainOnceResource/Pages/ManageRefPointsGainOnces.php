<?php

namespace App\Filament\Resources\RefPointsGainOnceResource\Pages;

use App\Filament\Resources\RefPointsGainOnceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRefPointsGainOnces extends ManageRecords
{
    protected static string $resource = RefPointsGainOnceResource::class;

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
