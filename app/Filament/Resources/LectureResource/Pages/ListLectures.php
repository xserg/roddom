<?php

namespace App\Filament\Resources\LectureResource\Pages;

use App\Filament\Resources\LectureResource;
use App\Models\Lecture;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLectures extends ListRecords
{
    protected static string $resource = LectureResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return Lecture::query()
            ->with(['rates', 'lector', 'category']);
    }

    protected function getTableFiltersFormColumns(): int
    {
        return 2;
    }
}
