<?php

namespace App\Filament\Resources\WizardResource\Pages;

use App\Filament\Resources\WizardResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWizards extends ManageRecords
{
    protected static string $resource = WizardResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
