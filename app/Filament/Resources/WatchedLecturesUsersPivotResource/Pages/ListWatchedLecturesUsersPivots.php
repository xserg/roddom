<?php

namespace App\Filament\Resources\WatchedLecturesUsersPivotResource\Pages;

use App\Filament\Resources\WatchedLecturesUsersPivotResource;
use App\Filament\Resources\WatchedLecturesUsersPivotResource\Widgets\LastDayViewsWidget;
use App\Filament\Resources\WatchedLecturesUsersPivotResource\Widgets\LectureViewsChart;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWatchedLecturesUsersPivots extends ListRecords
{
    protected static string $resource = WatchedLecturesUsersPivotResource::class;


    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['lecture' => fn ($q) => $q->withoutGlobalScopes()]);
    }

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
