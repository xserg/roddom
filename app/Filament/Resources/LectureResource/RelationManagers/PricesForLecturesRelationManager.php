<?php

namespace App\Filament\Resources\LectureResource\RelationManagers;

use App\Models\Period;
use App\Traits\MoneyConversion;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component as Livewire;

class PricesForLecturesRelationManager extends RelationManager
{
    use MoneyConversion;

    protected static string $relationship = 'pricesForLectures';
    protected static ?string $title = 'Цены лекции';
    protected static ?string $recordTitleAttribute = 'id';

    protected function getTableDescription(): string|Htmlable|null
    {
        return 'Приоритет выше чем у "общих" цен';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::priceField('price'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_id')
                    ->formatStateUsing(
                        fn (string $state): string => Period::firstWhere('id', $state)->length
                    )
                    ->label('Период покупки, дней'),

                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(
                        fn (string $state): string => number_format($state / 100, 2, thousands_separator: '')
                    )->label('Цена за одну лекцию этой категории, рублей'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

                Tables\Actions\AttachAction::make()
                    ->label('добавить период и цену')
                    ->disableAttachAnother()
                    ->preloadRecordSelect()
                    ->disabled(function (Livewire $livewire) {
                        $lecture = $livewire->ownerRecord;
                        $periods = $lecture->pricesForLectures;
                        if ($periods->count() == Period::all()->count()) {
                            return true;
                        }

                        return false;
                    })
                    ->form(fn (AttachAction $action): array => [
                        $action
                            ->getRecordSelect()
                            ->searchable(false)
                            ->options(function (Livewire $livewire) {
                                $lecture = $livewire->ownerRecord;
                                $periodsAlreadyAttached = $lecture
                                    ?->pricesForLectures
                                    ?->pluck('id')
                                    ?->toArray();

                                if (is_null($periodsAlreadyAttached)) {
                                    return Period::all()->pluck('length', 'id');
                                } else {
                                    return Period::query()->whereNotIn('id', $periodsAlreadyAttached)
                                        ->pluck('length', 'id');
                                }
                            })
                            ->label('период, дней'),

                        self::priceField('price')
                            ->label('цена, рублей'),
                    ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                //                Tables\Actions\DetachBulkAction::make()
                //                    ->before(function (Tables\Actions\DetachBulkAction $action, $records) {
                //                        if ($records->count() > 3) {
                //                            $action->cancel();
                //                        }
                //                    }),
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
