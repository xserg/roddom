<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Родительская категория (оставьте \'Выберите вариант\', если у категории нет родительской)')
                            ->options(
                                Category::mainCategories()->pluck('title', 'id')
                            )
                            ->default(0),
                        Forms\Components\TextInput::make('title')
                            ->reactive()
                            ->afterStateUpdated(function (Closure $set, $state) {
                                $set('slug', Str::slug($state));
                            })
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->disableToolbarButtons(['attachFiles',])
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('info')
                            ->maxLength(65535),
                    ]),
                Forms\Components\TextInput::make('slug')
                    ->label('Слаг категории, заполняется автоматически с наименования')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('preview_picture')
                    ->directory('categories')
                    ->dehydrateStateUsing(fn($state) => config('app.url') . '/storage/' . Arr::first($state))
                    ->maxSize(10240)
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Наименование')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('parentCategory.title')
                    ->label('Родительская категория')
                    ->sortable()
                    ->searchable(),
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
            RelationManagers\CategoryPricesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
