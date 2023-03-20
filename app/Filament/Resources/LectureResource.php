<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Models\Category;
use App\Models\Lecture;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Select::make('lector_id')
                        ->relationship('lector', 'name')
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->options(Category::subcategories()->get()->pluck('title', 'id'))
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('video_id')
                        ->label('kinescope id видео')
                        ->required(),
                ])->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\RichEditor::make('description')
                        ->maxLength(65535),
                    Forms\Components\FileUpload::make('preview_picture')
                        ->directory('lectures')
                        ->dehydrateStateUsing(fn($state) => config('app.url') . '/storage/' . Arr::first($state))
                        ->maxSize(10240)
                        ->image(),
                    Forms\Components\Toggle::make('is_published')
                        ->required()
                        ->label('опубликованная'),
                    Forms\Components\Toggle::make('is_free')
                        ->label('бесплатная')
                        ->required(),
                    Forms\Components\Toggle::make('is_recommended')
                        ->label('рекомендованная')
                        ->required(),
                ]),

//                Repeater::make('pricesInPromoPacks')
//                    ->defaultItems(2)
//                    ->relationship()
//                    ->schema([
//                        Forms\Components\TextInput::make('lecture_id')
//                            ->required()
//                            ->placeholder(fn(\Closure $get) => $get('id')),
//                        Forms\Components\Select::make('promo_id')
//                            ->options(['1' => 1])
//                            ->default('1')
//                            ->hidden()
//                            ->disabled(),
//                        Forms\Components\TextInput::make('period_id')
//                            ->required(),
//                        Forms\Components\TextInput::make('price')
//                            ->required(),
//                    ])
//                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => $record->title),
                Tables\Columns\TextColumn::make('category.title')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => $record->category->title),
                Tables\Columns\TextColumn::make('lector.name')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => $record->lector->name),
                Tables\Columns\ImageColumn::make('preview_picture'),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_promo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('is_free')
                    ->query(fn(Builder $query): Builder => $query->where('is_free', true))
                    ->label('бесплатные'),
                Filter::make('is_recommended')
                    ->query(fn(Builder $query): Builder => $query->where('is_recommended', true))
                    ->label('рекомендованные'),
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
            RelationManagers\PromoLecturesPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLectures::route('/'),
            'create' => Pages\CreateLecture::route('/create'),
            'edit' => Pages\EditLecture::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
