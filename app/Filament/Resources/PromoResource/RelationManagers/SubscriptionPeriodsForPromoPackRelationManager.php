<?php

namespace App\Filament\Resources\PromoResource\RelationManagers;

use App\Models\Period;
use App\Repositories\PromoRepository;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPeriodsForPromoPackRelationManager extends RelationManager
{
    protected static ?string $title = 'Общие цены, акционный пак';
    protected static string $relationship = 'subscriptionPeriodsForPromoPack';
    protected static ?string $recordTitleAttribute = 'period_id';

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(fn (?Model $record, string $context) => [
                self::priceField('price_for_one_lecture'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //                Tables\Columns\TextColumn::make('lecture title')
                //                    ->formatStateUsing(
                //                        function (callable $get) {
                //                            $lecture_id = $get('lecture_id'); // Store the value of the `email` field in the `$email` variable.
                //                            return Lecture::firstWhere('id', $lecture_id)->title;
                //                        }
                //                    )
                //                    ->searchable()
                //                    ->label('id лекции')
                //                    ->searchable(),

                Tables\Columns\TextColumn::make('period_id')
                    ->formatStateUsing(
                        fn (string $state): string => Period::firstWhere('id', $state)->length
                    )
                    ->label('Период покупки, дней'),

                Tables\Columns\TextColumn::make('price')
                    ->getStateUsing(
                        function (?Model $record) {
                            return app(PromoRepository::class)
                                ->calculatePromoPackPriceForPeriod(1, $record->period_id);
                        }
                    )
                    ->label('Цена, рублей'),

                Tables\Columns\TextColumn::make('price_for_one_lecture')
                    ->formatStateUsing(
                        fn (string $state): string => number_format($state / 100, 2, thousands_separator: '')
                    )
                    ->label('Цена за одну промо лекцию, рублей')
                    ->weight('bold')
                    ->sortable(),
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
                ->decimalPlaces(2)
                ->decimalSeparator('.')
            );
    }
}
