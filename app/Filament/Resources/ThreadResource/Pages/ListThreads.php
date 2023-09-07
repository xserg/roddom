<?php

namespace App\Filament\Resources\ThreadResource\Pages;

use App\Filament\Resources\ThreadResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListThreads extends ListRecords
{
    protected static string $resource = ThreadResource::class;

    protected function getActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}
