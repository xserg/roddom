<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppHelpPageResource\Pages;
use App\Filament\Resources\AppHelpPageResource\RelationManagers;
use App\Models\AppHelpPage;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppHelpPageResource extends Resource
{
    protected static ?string $model = AppHelpPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Страница "Помощь"';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Приложение';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('text')
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('text')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => $record->text),
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
            'index' => Pages\ListAppHelpPages::route('/'),
            'create' => Pages\CreateAppHelpPage::route('/create'),
            'edit' => Pages\EditAppHelpPage::route('/{record}/edit'),
        ];
    }
}
