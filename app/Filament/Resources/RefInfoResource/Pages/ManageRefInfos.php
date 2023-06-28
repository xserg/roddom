<?php

namespace App\Filament\Resources\RefInfoResource\Pages;

use App\Filament\Resources\RefInfoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRefInfos extends ManageRecords
{
    protected static string $resource = RefInfoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
