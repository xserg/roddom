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
    protected static string $relationship = 'referrals';
    protected static ?string $recordTitleAttribute = 'email';
    protected static ?string $title = 'Рефералы';

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
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('name')
                    ->url(function (?Model $record): string {
                        return route('filament.resources.users.edit', ['record' => $record?->id]);
                    }),
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
