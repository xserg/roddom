<?php

namespace App\Filament\Resources\PromoResource\Pages;

use App\Filament\Resources\PromoResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPromo extends EditRecord
{
    protected static string $resource = PromoResource::class;

    protected function getActions(): array
    {
        return [
            //            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->visible(false);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->visible(false);
    }
}
