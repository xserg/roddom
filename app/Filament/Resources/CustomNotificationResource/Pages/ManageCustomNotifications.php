<?php

namespace App\Filament\Resources\CustomNotificationResource\Pages;

use App\Filament\Resources\CustomNotificationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Actions\Action;

class ManageCustomNotifications extends ManageRecords
{
    protected static string $resource = CustomNotificationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
