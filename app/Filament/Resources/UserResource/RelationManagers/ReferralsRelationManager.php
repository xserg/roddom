<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Position;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';
    protected static ?string $recordTitleAttribute = 'email';
    protected static ?string $title = 'Рефералы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('имя'),
                Tables\Columns\TextColumn::make('email')
                    ->label('email'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Action::make('Страница_пользователя')
                    ->url(fn (User $record): string => route('filament.resources.users.edit', $record))
                    ->openUrlInNewTab()
            ])
            ->bulkActions([
            ]);
    }

    protected function getTableActionsPosition(): ?string
    {
        return Position::BeforeCells;
    }
}
