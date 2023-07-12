<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\UserResource\Widgets\LectureViews;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class WatchedLecturesHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'watchedLecturesHistory';
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $title = 'История просмотров лекций';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('pivot_created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Лекция')
                    ->searchable(isIndividual: true, isGlobal: false),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Просмотрена')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('pivot_created_at', $direction);
                    })
            ])
            ->filters([
                DateRangeFilter::make('date')
                    ->useColumn((new User())->watchedLecturesHistory()->getTable() . '.created_at')
                    ->label('Фильтровать по дате'),
            ], layout: Layout::AboveContent,)
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
