<?php

namespace App\Filament\Resources\AppPolicyResource\Pages;

use App\Filament\Resources\AppPolicyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppPolicies extends ListRecords
{
    protected static string $resource = AppPolicyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
