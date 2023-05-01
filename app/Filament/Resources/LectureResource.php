<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\LectureType;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as Livewire;
use Filament\Forms\Components\Component;

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Лекции';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Лекции';
    protected static ?string $pluralModelLabel = 'Лекции';
    protected static ?string $modelLabel = 'Лекция';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('id')
                        ->label('ID, заполняется автоматически')
                        ->disabled()
                        ->visible(false),
                    Forms\Components\TextInput::make('title')
                        ->label('Наименование лекции')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('lector_id')
                        ->label('Лектор')
                        ->relationship('lector', 'name')
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->label('Подкатегория лекции')
                        ->options(Category::subcategories()->get()->pluck('title', 'id'))
                        ->required(),
                ])->columns(2),
                Forms\Components\Section::make('content')
                    ->schema([
                        Forms\Components\Select::make('type_id')
                            ->options(LectureType::all()->pluck('title', 'id')),
                        Forms\Components\TextInput::make('video_id')
                            ->label('kinescope id видео(должно быть уникальным)')
                            ->required(),
                    ]),
                Forms\Components\Card::make([
                    Forms\Components\RichEditor::make('description')
                        ->label('Описание лекции')
                        ->toolbarButtons([
                            'bold',
                            'h2',
                            'h3',
                            'italic',
                            'redo',
                            'strike',
                            'undo',
                            'preview'
                        ])
                        ->maxLength(65535),
                    Forms\Components\FileUpload::make('preview_picture')
                        ->directory('images/lectures')
                        ->maxSize(10240)
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('4:3')
                        ->imageResizeTargetWidth('640')
                        ->imageResizeTargetHeight('480'),

                    Forms\Components\Toggle::make('is_published')
                        ->required()
                        ->label('опубликованная'),
                    Forms\Components\Toggle::make('is_free')
                        ->label('бесплатная')
                        ->disabled(function (?Lecture $record, Component $component, $context) {
                            if ($context == 'create') return false;
                            if ($record->isPromo) {
                                $component->state(false);
                                return true;
                            }
                            return false;
                        })
                        ->required()
                        ->reactive(),
                    Forms\Components\Toggle::make('is_recommended')
                        ->label('рекомендованная')
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('id')
//                    ->label('ID лекции')
//                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Наименование')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => $record->title)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Подкатегория')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => isset($record->category) ? $record->category->title : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lector.name')
                    ->label('Лектор')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => isset($record->lector) ? $record->lector->name : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate_avg')
                    ->getStateUsing(
                        function (?Lecture $record): ?string {
                            return round($record->rates['rate_avg'], 1) ?: 'нет оценок';
                        }
                    )
                    ->label('Рейтинг, из 10'),
//                Tables\Columns\ImageColumn::make('preview_picture')
//                    ->label('Превью лекции'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубликована')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_promo')
                    ->label('Акционная')
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
//                Tables\Actions\DissociateBulkAction::make(),
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
