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
        $payedLecturesCount = Lecture::payed()->count();
        $allLecturesCount = Lecture::count();
        $form = app(LectureService::class)->getEverythingPackPricesResource();

        return [
            'form' => $form,
            'payed_lectures_count' => $payedLecturesCount,
            'all_lectures_count' => $allLecturesCount,
        ];
    }
}
