<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppPolicyResource\Pages;
use App\Filament\Resources\AppPolicyResource\RelationManagers;
use App\Models\AppPolicy;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class AppPolicyResource extends Resource
{
    protected static ?string $model = AppPolicy::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Страница "Политики"';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Приложение';

    protected static ?string $label = 'Страница "Политики"';

    protected static ?string $pluralModelLabel = 'Страница "Политик"';

    protected static ?string $modelLabel = 'Страница "Политики"';



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
                Forms\Components\Toggle::make('is_active')
                    ->label('активна'),    
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
                Tables\Columns\ToggleColumn::make('is_active'),    
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
            'index' => Pages\ListAppPolicies::route('/'),
            'create' => Pages\CreateAppPolicy::route('/create'),
            'edit' => Pages\EditAppPolicy::route('/{record}/edit'),
        ];
    }    
}
