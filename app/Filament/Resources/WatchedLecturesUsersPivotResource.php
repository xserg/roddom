<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatchedLecturesUsersPivotResource\Pages;
use App\Filament\Resources\WatchedLecturesUsersPivotResource\RelationManagers;
use App\Models\Feedback;
use App\Models\User;
use App\Models\WatchedLecturesUsersPivot;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WatchedLecturesUsersPivotResource extends Resource
{
    protected static ?string $model = WatchedLecturesUsersPivot::class;
    protected static ?string $label = 'Просмотр лекций';
    protected static ?string $pluralLabel = 'Просмотры лекций';
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Лекции';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required(),
                Forms\Components\TextInput::make('lecture_id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->limit(15)
                    ->formatStateUsing(function (?Model $record) {
                        $user = $record->user;
                        return $user->name ?? $user->email;
                    })
                    ->tooltip(fn (?Model $record): string => $record->user->email)
                    ->url(function (?Model $record): string {
                        return route('filament.resources.users.edit', ['record' => $record->user]);
                    })
                    ->label('пользователь'),
                Tables\Columns\TextColumn::make('lecture.title')
                    ->limit(35)
                    ->tooltip(fn (?Model $record): string => $record->lecture->title)
                    ->url(function (?Model $record): string {
                        return route('filament.resources.lectures.edit', ['record' => $record->lecture]);
                    })
                    ->label('лекция'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('дата')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWatchedLecturesUsersPivots::route('/'),
            'create' => Pages\CreateWatchedLecturesUsersPivot::route('/create'),
            'edit' => Pages\EditWatchedLecturesUsersPivot::route('/{record}/edit'),
        ];
    }
}
