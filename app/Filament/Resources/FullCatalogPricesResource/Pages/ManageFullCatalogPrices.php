<?php

namespace App\Filament\Resources\FullCatalogPricesResource\Pages;

use App\Filament\Resources\FullCatalogPricesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFullCatalogPrices extends ManageRecords
{
    protected static string $resource = FullCatalogPricesResource::class;

    protected function getActions(): array
    {
        return [
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
    protected function getFooterWidgets(): array
    {
        return [
            FullCatalogPricesResource\Widgets\FullCatalogPrices::class
        ];
    }
}
