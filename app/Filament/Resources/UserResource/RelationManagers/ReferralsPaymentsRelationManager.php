<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\User;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class ReferralsPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'refPointsGetPayments';
    protected static ?string $recordTitleAttribute = 'email';
    protected static ?string $title = 'Начисление бебикоинов';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reason')
                    ->label('описание операции')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payer_id')
                    ->formatStateUsing(function (?string $state) {
                        $user = User::find($state);
                        return $user->name ?? $user->email;
                    })
                    ->url(function (?Model $record): string {
                        $route = route('filament.resources.users.edit', ['record' => $record->payer_id]);
                        return $route;
                    })
                    ->label('пользователь, реферал'),
                Tables\Columns\TextColumn::make('ref_points')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('бебикоинов начислено')
                    ->sortable(),
                Tables\Columns\TextColumn::make('depth_level')
                    ->formatStateUsing(function (?string $state) {
                        return $state ? "реферал {$state} уровня" : '';
                    })
                    ->label('уровень')
                    ->sortable(),
                Tables\Columns\TextColumn::make('percent')
                    ->label('процент от стоимости')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('цена')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_to_pay')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('оплачено реальной валютой')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('начислено в')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
