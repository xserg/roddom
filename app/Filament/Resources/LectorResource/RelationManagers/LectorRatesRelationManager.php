<?php

namespace App\Filament\Resources\LectorResource\RelationManagers;

use App\Filament\Resources\UserResource;
use App\Jobs\UpdateAverageLectorRateJob;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class LectorRatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Оценки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->formatStateUsing(fn (?Model $record) => $record->user->name ?? $record->user->email)
                    ->url(fn (?Model $record) => UserResource::getUrl('edit', ['record' => $record->user->id]))
                    ->label('Пользователь'),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка, из 10'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn (?Model $record) => UpdateAverageLectorRateJob::dispatch($record->lector))
            ])
            ->bulkActions([
            ]);
    }
}
