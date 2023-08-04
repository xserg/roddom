<?php

namespace App\Filament\Resources\LectureResource\RelationManagers;

use App\Filament\Resources\UserResource;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LectureRatesRelationManager extends RelationManager
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
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->formatStateUsing(fn (?Model $record) => $record->user->name ?? $record->user->email)
                    ->url(fn (?Model $record) => UserResource::getUrl('edit', ['record' => $record->user->id]))
                    ->label('Пользователь'),
                Tables\Columns\TextColumn::make('rating')
                    ->label('оценка, из 10'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
