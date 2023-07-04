<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FullCatalogPricesResource\Pages;
use App\Models\FullCatalogPrices;
use App\Traits\MoneyConversion;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class FullCatalogPricesResource extends Resource
{
    use MoneyConversion;

    protected static ?string $model = FullCatalogPrices::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Цены на весь каталог';
    protected static ?int $navigationSort = 5;
    protected static ?string $label = 'Цены на весь каталог';
    protected static ?string $pluralModelLabel = 'Цены на весь каталог';
    protected static ?string $modelLabel = 'Цена';
    protected static ?string $navigationGroup = 'Лекции';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::priceField('price_for_one_lecture')
                    ->label('Цена лекции, рублей'),
                self::priceField('price_for_one_lecture_promo')
                    ->label('Акционная цена лекции, рублей'),
                Forms\Components\Toggle::make('is_promo')
                    ->label('Акционная')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_id')
                    ->formatStateUsing(
                        fn (?Model $record): string => $record->period->length
                    )
                    ->label('Период покупки, дней'),
                Tables\Columns\TextColumn::make('price_for_one_lecture')
                    ->formatStateUsing(
                        fn (?string $state): string => self::coinsToRoubles($state)
                    )
                    ->label('Цена лекции, рублей')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('price_for_one_lecture_promo')
                    ->formatStateUsing(
                        fn (?string $state): string => $state ? self::coinsToRoubles($state) : 'не указана'
                    )
                    ->label('Акционная цена лекции, рублей')
                    ->weight('bold'),

                Tables\Columns\ToggleColumn::make('is_promo')
                    ->label('Акционная')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFullCatalogPrices::route('/'),
        ];
    }

    public static function priceField(string $name): Forms\Components\Field
    {
        return Forms\Components\TextInput::make($name)
            ->required()
            ->afterStateHydrated(function (TextInput $component, $state) {
                $component->state(number_format($state / 100, 2, thousands_separator: ''));
            })
            ->dehydrateStateUsing(fn ($state) => $state * 100)
            ->numeric()
            ->mask(fn (TextInput\Mask $mask) => $mask
                ->numeric()
                ->decimalPlaces(2) // Set the number of digits after the decimal point.
                ->decimalSeparator('.') // Add a separator for decimal numbers.
            );
    }
}
