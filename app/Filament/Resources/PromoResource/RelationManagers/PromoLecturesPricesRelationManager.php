<?php

namespace App\Filament\Resources\PromoResource\RelationManagers;

use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Traits\MoneyConversion;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Model;
use Closure;
use Livewire\Component as Livewire;

class PromoLecturesPricesRelationManager extends RelationManager
{
    use MoneyConversion;

    protected static ?string $title = 'Цены на каждую акционную лекцию';

    protected static string $relationship = 'pricesForPromoLectures';
    protected static ?string $inverseRelationship = 'pricesInPromoPacks';
    protected static ?string $recordTitleAttribute = 'id';

    protected bool $allowsDuplicates = true;

    public function getTableRecordTitle(Model $record): string
    {
        return 'цены на промо лекции';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(fn(?Model $record, string $context) => [
                self::priceField('price')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lecture_id')
                    ->label('id и название лекции')
                    ->formatStateUsing(fn(Lecture $record, string $state): string => $state . '. ' . $record->title)
                    ->sortable(),

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
                        fn(string $state): string => Period::firstWhere('id', $state)->length
                    )
                    ->label('Период покупки, дней')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(
                        fn(string $state): string => number_format($state / 100, 2, thousands_separator: '')
                    )
                    ->label('Цена, рублей')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('добавить лекцию, период и цену')
                    ->disableAttachAnother()
                    ->preloadRecordSelect()
                    ->form(fn(AttachAction $action): array => [

                        Forms\Components\Select::make('lecture_id')
                            ->options(function (Livewire $livewire) {
                                $promo = $livewire->ownerRecord;

                                $promoLectures = $promo->pricesForPromoLectures
                                    ->pluck('id')
                                    ->countBy()
                                    ->filter(function (int $value, int $key) {
                                        return $value == 3;
                                    })
                                    ->keys();

                                $filtered = Lecture::all()
                                    ->whereNotIn('id', $promoLectures)
                                    ->pluck('id_title', 'id');

                                return $filtered;
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('period_id', null);
                            }),

                        Forms\Components\Select::make('period_id')
                            ->required()
                            ->options(function (callable $get, Livewire $livewire) {
                                $promo = $livewire->ownerRecord;
                                $lectureId = $get('lecture_id');

                                $periodsExisting = $promo
                                    ->pricesForPromoLectures()
                                    ->wherePivot('lecture_id', $lectureId)
                                    ->get()
                                    ->pluck('pivot.period_id');

                                if (!$periodsExisting) {
                                    return Period::all()->pluck('length', 'id');
                                }

                                $periods = Period::all()
                                    ->whereNotIn('id', $periodsExisting)
                                    ->pluck('length', 'id');

                                return $periods;
                            })
                            ->reactive(),

                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->afterStateHydrated(
                                fn(TextInput $component, $state) => $component->state(number_format($state / 100, 2, thousands_separator: ''))
                            )
                            ->dehydrateStateUsing(fn($state) => $state * 100),

                        $action->getRecordSelect()->default(Promo::first()->id)->disabled(),

                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn(Model $record): string => route('filament.resources.lectures.edit', ['record' => $record->lecture_id]);
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
                ->decimalPlaces(2)
                ->decimalSeparator('.')
            );
    }
}
