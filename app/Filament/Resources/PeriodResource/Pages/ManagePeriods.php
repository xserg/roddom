<?php

namespace App\Filament\Resources\PeriodResource\Pages;

use App\Filament\Resources\PeriodResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePeriods extends ManageRecords
{
    protected static string $resource = PeriodResource::class;

    protected function getActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
