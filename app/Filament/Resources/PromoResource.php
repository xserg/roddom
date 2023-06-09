<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages;
use App\Filament\Resources\PromoResource\RelationManagers;
use App\Models\Promo;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'Акционный пак лекций';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Акционный пак лекций';
    protected static ?string $pluralModelLabel = 'Акции';
    protected static ?string $modelLabel = 'Акция';
    protected static ?string $navigationGroup = 'Лекции';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubscriptionPeriodsForPromoPackRelationManager::class,
            RelationManagers\PromoLecturesPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromos::route('/'),
            'create' => Pages\CreatePromo::route('/create'),
            'edit' => Pages\EditPromo::route('/{record}/edit'),
        ];
    }
}
