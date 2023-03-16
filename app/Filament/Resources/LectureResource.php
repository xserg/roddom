<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Models\Category;
use App\Models\Lecture;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Livewire\TemporaryUploadedFile;

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
                    Forms\Components\RichEditor::make('description')
                        ->maxLength(65535),
                    Forms\Components\FileUpload::make('preview_picture')
                        ->directory('lectures')
                        ->dehydrateStateUsing(fn($state) => config('app.url') . '/storage/' . Arr::first($state))
                        ->maxSize(10240)
                        ->image(),
                    Forms\Components\TextInput::make('video_id')
                        ->label('kinescope id видео')
                        ->required(),
                ]),
                Forms\Components\Card::make([
                    Forms\Components\Toggle::make('is_published')
                        ->required()
                        ->label('опубликованная'),
                    Forms\Components\Toggle::make('is_free')
                        ->label('бесплатная')
                        ->required(),
                    Forms\Components\Toggle::make('is_recommended')
                        ->label('рекомендованная')
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lector.name'),
                Tables\Columns\TextColumn::make('category.title'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('preview_picture'),
                Tables\Columns\TextColumn::make('video_id'),
                Tables\Columns\IconColumn::make('is_free')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_recommended')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListLectures::route('/'),
            'create' => Pages\CreateLecture::route('/create'),
            'edit' => Pages\EditLecture::route('/{record}/edit'),
        ];
    }
}
