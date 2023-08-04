<?php

namespace App\Filament\Resources\WatchedLecturesUsersPivotResource\Widgets;

use App\Models\WatchedLecturesUsersPivot;
use Filament\Widgets\BarChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class LectureViewsChart extends BarChartWidget
{
    protected static ?string $heading = 'Количество просмотров лекций';
    public ?string $filter = 'week';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $start = match ($activeFilter) {
            'days' => now()->subDays(2),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
        };

        $data = Trend::model(WatchedLecturesUsersPivot::class)
            ->between(
                start: $start,
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Просмотры лекций',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'borderWidth' => 1
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::createFromDate($value->date)->translatedFormat('M-d')),
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'days' => 'Три дня',
            'week' => 'Неделя',
            'month' => 'Месяц',
            'year' => 'Год',
        ];
    }
}
