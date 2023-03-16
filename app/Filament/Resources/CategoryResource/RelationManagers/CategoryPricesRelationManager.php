<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Period;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class CategoryPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'categoryPrices';

    protected static ?string $recordTitleAttribute = 'period_length';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('price_for_pack')
                    ->required()
                    ->afterStateHydrated(function (TextInput $component, $state) {
                        $component->state(number_format($state / 100, 2, thousands_separator: ''));
                    })
                    ->dehydrateStateUsing(fn($state) => $state * 100)
                    ->numeric()
                    ->mask(fn(TextInput\Mask $mask) => $mask
                        ->numeric()
                        ->decimalPlaces(2) // Set the number of digits after the decimal point.
                        ->decimalSeparator('.') // Add a separator for decimal numbers.
                    ),

                Forms\Components\TextInput::make('price_for_one_lecture')
                    ->required()
                    ->afterStateHydrated(function (TextInput $component, $state) {
                        $component->state(number_format($state / 100, 2, thousands_separator: ''));
                    })
                    ->dehydrateStateUsing(fn($state) => $state * 100)
                    ->numeric()
                    ->mask(fn(TextInput\Mask $mask) => $mask
                        ->numeric()
                        ->decimalPlaces(2) // Set the number of digits after the decimal point.
                        ->decimalSeparator('.') // Add a separator for decimal numbers.
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_id')
                    ->formatStateUsing(
                        fn(string $state): string => Period::firstWhere('id', $state)->length
                    )
                    ->label('Период покупки, дней'),

                Tables\Columns\TextColumn::make('price_for_pack')
                    ->formatStateUsing(
                        fn(string $state): string => number_format($state / 100, 2, thousands_separator: '')
                    )
                    ->label('Цена за всю категорию, рублей'),

                Tables\Columns\TextColumn::make('price_for_one_lecture')
                    ->formatStateUsing(
                        fn(string $state): string => number_format($state / 100, 2, thousands_separator: '')
                    )
                    ->label('Цена за одну лекцию этой категории, рублей'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
