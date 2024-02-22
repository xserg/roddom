<?php

namespace App\Filament\Resources\WatchedLecturesUsersPivotResource\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\WatchedLecturesUsersPivot;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LastDayViewsWidget extends BaseWidget
{
    protected static ?string $heading = 'Просмотров за последние 24 часа';

    protected function getTableQuery(): Builder
    {
        return WatchedLecturesUsersPivot::query()->select(
            DB::raw('MIN(id) as id'),
            DB::raw('user_id'),
            DB::raw('count(*) as total'))
            ->where('created_at', '>', now()->subDay())
            ->groupBy('user_id');
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'total';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user_id')
                ->formatStateUsing(fn (?Model $record) => $record->user?->name ?? $record->user?->email)
                ->url(fn (?Model $record) => UserResource::getUrl('edit', ['record' => $record->user_id]))
                ->label('Пользователь'),
            Tables\Columns\TextColumn::make('total')->label('просмотров')
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }
}
