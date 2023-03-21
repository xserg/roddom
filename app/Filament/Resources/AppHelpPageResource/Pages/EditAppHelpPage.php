<?php

namespace App\Filament\Resources\AppHelpPageResource\Pages;

use App\Filament\Resources\AppHelpPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppHelpPage extends EditRecord
{
    protected static string $resource = AppHelpPageResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
