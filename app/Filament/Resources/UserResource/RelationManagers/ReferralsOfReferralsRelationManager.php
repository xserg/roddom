<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\User;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\Position;
use Illuminate\Database\Eloquent\Model;

class ReferralsOfReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referralsOfReferrals';
    protected static ?string $title = 'Рефералы рефералов';
    protected static ?string $recordTitleAttribute = 'email';

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
                Tables\Columns\TextColumn::make('referrer_id')
                    ->formatStateUsing(function (?string $state) {
                        $user = User::find($state);
                        return $user->name ?? $user->email;
                    })
                    ->url(function (?Model $record): string {
                        $route = route('filament.resources.users.edit', ['record' => $record->referrer_id]);
                        return $route;
                    })
                    ->label('промежуточный реферал'),
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
