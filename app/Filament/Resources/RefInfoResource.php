<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefInfoResource\Pages;
use App\Filament\Resources\RefInfoResource\RelationManagers;
use App\Models\RefInfo;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefInfoResource extends Resource
{
    protected static ?string $model = RefInfo::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Процентные начисления';
    protected static ?string $navigationGroup = 'Партнерская программа';
    protected static ?string $pluralLabel = 'Процентные начисления';
    protected static ?string $recordTitleAttribute = 'depth_level';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('percent')
                    ->integer()
                    ->minValue(1)
                    ->maxValue(100)
                    ->label('процент начислений')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('depth_level')
                    ->label('уровень реферала')
                    ->formatStateUsing(function (string $state) {
                        if ($state === '1.1') {
                            return '1 уровень, горизонтальный';
                        }
                        return $state ? $state . ' уровень' : 'уровень не определен';
                    }),
                Tables\Columns\TextColumn::make('percent')
                    ->label('процент отчислений')
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
            'index' => Pages\ManageRefInfos::route('/'),
        ];
    }
}
