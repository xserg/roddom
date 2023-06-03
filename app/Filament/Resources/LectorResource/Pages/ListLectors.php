<?php

namespace App\Filament\Resources\LectorResource\Pages;

use App\Filament\Resources\LectorResource;
use App\Models\Lector;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLectors extends ListRecords
{
    protected static string $resource = LectorResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return Lector::query()
            ->with(['rates']);
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100];
    }
}
