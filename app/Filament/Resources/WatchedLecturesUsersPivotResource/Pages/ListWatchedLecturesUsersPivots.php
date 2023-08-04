<?php

namespace App\Filament\Resources\WatchedLecturesUsersPivotResource\Pages;

use App\Filament\Resources\WatchedLecturesUsersPivotResource;
use App\Filament\Resources\WatchedLecturesUsersPivotResource\Widgets\LastDayViewsWidget;
use App\Filament\Resources\WatchedLecturesUsersPivotResource\Widgets\LectureViewsChart;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWatchedLecturesUsersPivots extends ListRecords
{
    protected static string $resource = WatchedLecturesUsersPivotResource::class;

    protected function getActions(): array
    {
        return [
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LectureViewsChart::class,
            LastDayViewsWidget::class
        ];
    }
}
