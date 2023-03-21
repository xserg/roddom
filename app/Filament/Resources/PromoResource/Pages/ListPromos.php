<?php

namespace App\Filament\Resources\PromoResource\Pages;

use App\Filament\Resources\PromoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromos extends ListRecords
{
    protected static string $resource = PromoResource::class;

    protected function getActions(): array
    {
        return [
        ];
    }
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
