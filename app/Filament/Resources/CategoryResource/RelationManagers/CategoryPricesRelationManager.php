<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Category;
use App\Models\Period;
use App\Models\SubcategoryPrices;
use App\Repositories\CategoryRepository;
use App\Traits\MoneyConversion;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class CategoryPricesRelationManager extends RelationManager
{
    use MoneyConversion;

    protected static string $relationship = 'categoryPrices';
    protected static ?string $inverseRelationship = 'category';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Цены категории';

    protected function getTableQuery(): Builder|Relation
    {
        return parent::getTableQuery()->with([
            'period',
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::priceField('price_for_one_lecture')
                    ->label('Общая цена лекции, рублей'),
                self::priceField('price_for_one_lecture_promo')
                    ->label('Общая цена лекции, акционная, рублей'),
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
                    ->label('Общая цена лекции, рублей')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('price_for_one_lecture_promo')
                    ->formatStateUsing(
                        fn (?string $state): string => $state ? self::coinsToRoubles($state) : 'не указана'
                    )
                    ->label('Общая цена лекции, акционная, рублей')
                    ->weight('bold'),
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

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
