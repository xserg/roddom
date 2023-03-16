<?php

namespace App\Filament\Resources\LectorResource\Pages;

use App\Filament\Resources\LectorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLector extends EditRecord
{
    protected static string $resource = LectorResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
