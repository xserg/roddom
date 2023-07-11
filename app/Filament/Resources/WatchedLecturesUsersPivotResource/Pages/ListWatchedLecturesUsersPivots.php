<?php

namespace App\Filament\Resources\WatchedLecturesUsersPivotResource\Pages;

use App\Filament\Resources\WatchedLecturesUsersPivotResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWatchedLecturesUsersPivots extends ListRecords
{
    protected static string $resource = WatchedLecturesUsersPivotResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
