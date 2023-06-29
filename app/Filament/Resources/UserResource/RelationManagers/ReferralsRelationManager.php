<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Feedback;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'refPointsGetPayments';
    protected static ?string $recordTitleAttribute = 'email';
    protected static ?string $title = 'Начисления реф поинтов';

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
                Tables\Columns\TextColumn::make('payer.name')
                    ->name('имя')
                    ->sortable(),
                Tables\Columns\TextColumn::make('depth_level')
                    ->formatStateUsing(fn (?string $state) => ((int) $state === 1 ? 'реферал' : $state === 2) ? 'реферал реферала' : '')
                    ->label('глубина')
                    ->sortable(),
                Tables\Columns\TextColumn::make('percent')
                    ->label('процент от стоимости')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ref_points')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('поинтов начислено')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn (?string $state) => $state / 100)
                    ->label('стоимость покупки')
                    ->sortable()

//                Tables\Columns\TextColumn::make('name')
//                    ->url(function (?Model $record): string {
//                        return route('filament.resources.users.edit', ['record' => $record?->id]);
//                    }),
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
