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
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Категории';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Категории';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?string $modelLabel = 'Категория';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->reactive()
                                    ->afterStateUpdated(function (Closure $set, $state) {
                                        $set('slug', Str::slug($state));
                                    })
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Наименование категории')
                                    ->columnSpan(1),
                                Forms\Components\Select::make('parent_id')
                                    ->label('Родительская категория (оставьте \'Выберите вариант\', если у категории нет родительской)')
                                    ->options(
                                        Category::mainCategories()->pluck('title', 'id')
                                    )
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('preview_picture')
                            ->directory('images/categories')
                            ->maxSize(10240)
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('4:3')
                            ->imageResizeTargetWidth('640')
                            ->imageResizeTargetHeight('480')
                            ->label('Превью картинка категории'),
                    ])->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\RichEditor::make('description')
                        ->toolbarButtons([
                            'bold',
                            'h2',
                            'h3',
                            'italic',
                            'redo',
                            'strike',
                            'undo',
                            'preview',
                        ])
                        ->maxLength(65535)
                        ->label('Описание категории'),
                    Forms\Components\Textarea::make('info')
                        ->maxLength(65535)
                        ->label('Блок "инфо" категории'),
                ])->columns(2),
                Forms\Components\TextInput::make('slug')
                    ->label('Слаг категории, заполняется автоматически с наименования')
                    ->unique(table: Category::class, ignoreRecord: true)
                    ->validationAttribute('"Слаг категории"')
                    ->required()
                    ->maxLength(255),
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CategoryPricesRelationManager::class,
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
