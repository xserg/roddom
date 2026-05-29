<?php

namespace App\Filament\Resources\AppPolicyResource\Pages;

use App\Filament\Resources\AppPolicyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppPolicy extends EditRecord
{
    protected static string $resource = AppPolicyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
