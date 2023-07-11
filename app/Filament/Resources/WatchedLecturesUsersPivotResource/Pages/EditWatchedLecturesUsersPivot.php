<?php

namespace App\Filament\Resources\WatchedLecturesUsersPivotResource\Pages;

use App\Filament\Resources\WatchedLecturesUsersPivotResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWatchedLecturesUsersPivot extends EditRecord
{
    protected static string $resource = WatchedLecturesUsersPivotResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
