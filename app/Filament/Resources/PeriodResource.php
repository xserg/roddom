<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodResource\Pages;
use App\Models\Period;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Position;

class PeriodResource extends Resource
{
    protected static ?string $model = Period::class;

    protected static ?string $navigationGroup = 'Приложение';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Периоды подписок';

    protected static ?int $navigationSort = 5;

    protected static ?string $label = 'Периоды подписок';

    protected static ?string $pluralModelLabel = 'Периоды';

    protected static ?string $modelLabel = 'Период';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('length')
                    ->label('длина подписки в днях')
                    ->required()
                    ->integer()
                    ->rules(['gt:0']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('length')
                    ->label('длина подписки в днях'),
                //                Tables\Columns\TextColumn::make('created_at')
                //                    ->dateTime(),
                //                Tables\Columns\TextColumn::make('updated_at')
                //                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->actionsPosition(Position::BeforeCells)
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePeriods::route('/'),
        ];
    }
}
