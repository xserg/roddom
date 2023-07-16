<?php

namespace App\Filament\Resources\WizardInfoResource\Pages;

use App\Filament\Resources\WizardInfoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWizardInfos extends ManageRecords
{
    protected static string $resource = WizardInfoResource::class;

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
