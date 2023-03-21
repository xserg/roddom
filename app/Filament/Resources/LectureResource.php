<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Models\Category;
use App\Models\Lecture;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as Livewire;

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
                        ->disabled(),
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
                        ->directory('images/lectures')
//                        ->directory(function (Closure $get, string $context) {
//                            if ($context == 'create') {
//                                $nextId = DB::select("show table status like 'lecture'")[0]->Auto_increment;
//                                return 'images/lectures' . '/' . $nextId;
//                            }
//                            return 'images/lectures' . '/' . $get('id');
//                        })
//                        ->afterStateHydrated(function (Closure $set, Forms\Components\FileUpload $component, $state) {
//                            $redundantStr = config('app.url') . '/storage/';
//
//                            if (is_null($state)) {
//                                return;
//                            }
//
//                            if (Str::contains($state, $redundantStr)) {
//                                $component->state([Str::remove($redundantStr, $state)]);
//                            } else {
//                                $component->state([$state]);
//                            }
//                        })
//                        ->dehydrateStateUsing(
//                            function (Closure $set, $state, Closure $get) {
//                                return config('app.url') . '/storage/' . Arr::first($state);
//                            })
//                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Closure $get, string $context): string {
//                            if ($context == 'create') {
//                                $nextId = DB::select("show table status like 'lectures'")[0]->Auto_increment;
//                                return (string)$nextId . '.' . $file->getClientOriginalExtension();
//                            }
//                            return (string)$get('id') . '.' . $file->getClientOriginalExtension();
//                        })
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
                        ->disabled(function (Lecture $record, $state) {
                            if ($record->isPromo) {
                                $state = false;
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
                    ->tooltip(fn(Model $record): string => isset($record->category) ? $record->category->title : ''),
                Tables\Columns\TextColumn::make('lector.name')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => isset($record->lector) ? $record->lector->name : ''),
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
                Tables\Actions\DissociateBulkAction::make(),
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
