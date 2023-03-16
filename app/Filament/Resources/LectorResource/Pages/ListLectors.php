<?php

namespace App\Filament\Resources\LectorResource\Pages;

use App\Filament\Resources\LectorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLectors extends ListRecords
{
    protected static string $resource = LectorResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
