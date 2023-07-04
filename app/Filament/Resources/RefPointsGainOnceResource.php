<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefPointsGainOnceResource\Pages;
use App\Filament\Resources\RefPointsGainOnceResource\RelationManagers;
use App\Models\RefPointsGainOnce;
use App\Traits\MoneyConversion;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefPointsGainOnceResource extends Resource
{
    use MoneyConversion;

    protected static ?string $model = RefPointsGainOnce::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Одномоментные начисления';
    protected static ?string $navigationGroup = 'Партнерская программа';
    protected static ?string $pluralLabel = 'Одномоментные начисления';
    protected static ?string $label = 'Одномоментные начисления';
    protected static ?string $recordTitleAttribute = 'user_type';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::priceField('points_gains')
                    ->label('Получает бэбикоинов'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_type')
                    ->formatStateUsing(function(?string $state){
                        return match ($state){
                            'referrer' => 'приглашающий, реферер',
                            'referral' => 'приглашенный, реферал'
                        };
                    })
                    ->label('Кто получает'),
                Tables\Columns\TextColumn::make('points_gains')
                    ->formatStateUsing(
                        fn (?string $state): string => self::coinsToRoubles($state)
                    )
                    ->label('Получает бебикоинов'),
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
            'index' => Pages\ManageRefPointsGainOnces::route('/'),
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
