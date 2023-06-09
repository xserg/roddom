<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppHelpPageResource\Pages;
use App\Models\AppHelpPage;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class AppHelpPageResource extends Resource
{
    protected static ?string $model = AppHelpPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Страница "Помощь"';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Приложение';

    protected static ?string $label = 'Страница "Помощь"';

    protected static ?string $pluralModelLabel = 'Страница "Помощь"';

    protected static ?string $modelLabel = 'Страница "Помощь"';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('заголовок'),
                Forms\Components\Textarea::make('text')
                    ->required()
                    ->maxLength(65535)
                    ->label('текст'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('заголовок'),
                Tables\Columns\TextColumn::make('text')
                    ->label('текст')
                    ->limit(25)
                    ->tooltip(fn (Model $record): string => $record->text),
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
