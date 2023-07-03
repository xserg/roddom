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
    protected static ?string $title = 'История начисления бебикоинов';

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
                Tables\Columns\TextColumn::make('payer_id')
                    ->formatStateUsing(function (?string $state) {
                        $user = User::find($state);
                        return $user->name ?? $user->email;
                    })
                    ->url(function (?Model $record): string {
                        $route = route('filament.resources.users.edit', ['record' => $record->payer_id]);
                        return $route;
                    })
                    ->label('имя'),
                Tables\Columns\TextColumn::make('depth_level')
                    ->formatStateUsing(function (?string $state) {
                        if ((int) $state === 1) {
                            return 'реферал';
                        } elseif ((int) $state === 2) {
                            return 'реферал реферала';
                        }

                        return $state;
                    })
                    ->label('глубина')
                    ->sortable(),
                Tables\Columns\TextColumn::make('percent')
                    ->label('процент от стоимости')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ref_points')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('бебикоинов начислено')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('стоимость покупки')
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
