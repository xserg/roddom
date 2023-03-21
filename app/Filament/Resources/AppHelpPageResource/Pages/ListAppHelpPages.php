<?php

namespace App\Filament\Resources\AppHelpPageResource\Pages;

use App\Filament\Resources\AppHelpPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppHelpPages extends ListRecords
{
    protected static string $resource = AppHelpPageResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
