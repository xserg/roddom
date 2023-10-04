<?php

namespace App\Filament\Resources\FullCatalogPricesResource\Widgets;

use App\Models\Lecture;
use App\Services\LectureService;
use Filament\Widgets\Widget;

class FullCatalogPrices extends Widget
{
    protected static string $view = 'filament.resources.full-catalog-resource.widgets.full-catalog-price';

    protected function getViewData(): array
    {
        $lecturesCount = Lecture::payed()->count();
        $form = app(LectureService::class)->getEverythingPackPricesResource();

        return [
            'form' => $form,
            'lectures_count' => $lecturesCount
        ];
    }
}
