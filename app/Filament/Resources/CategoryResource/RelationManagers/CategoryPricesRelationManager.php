<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Period;
use App\Models\SubcategoryPrices;
use App\Services\CategoryService;
use App\Traits\MoneyConversion;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class CategoryPricesRelationManager extends RelationManager
{
    use MoneyConversion;

    protected static string $relationship = 'categoryPrices';

    protected static ?string $inverseRelationship = 'category';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Цены категории';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                self::priceField('price_for_pack'),
                self::priceField('price_for_one_lecture')
                    ->label('цена за одну лекцию данной подкатегории'),
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
                Tables\Columns\TextColumn::make('price_pack')
                    ->getStateUsing(
                        function (?SubcategoryPrices $record): ?string {
                            $categoryId = $record->category_id;
                            $periodId = $record->period_id;
                            $price = app(CategoryService::class)
                                ->getCategoryPrice(
                                    $categoryId,
                                    $periodId);

                            return $price;
                        }
                    )
                    ->label('Цена за всю категорию, рублей'),
                Tables\Columns\TextColumn::make('lectures_count')
                    ->getStateUsing(
                        function (?SubcategoryPrices $record): string {
                            $category = $record->category;
                            $lecturesCount = $category->lectures()->count();
                            return $lecturesCount;
                        }
                    )->label('Количество лекций'),
                Tables\Columns\TextColumn::make('price_for_one_lecture')
                    ->formatStateUsing(
                        fn(string $state): string => self::coinsToRoubles($state)
                    )
                    ->label('Цена за одну лекцию этой категории, рублей')
                ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\AssociateAction::make()->preloadRecordSelect()
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
            ->dehydrateStateUsing(fn($state) => $state * 100)
            ->numeric()
            ->mask(fn(TextInput\Mask $mask) => $mask
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
