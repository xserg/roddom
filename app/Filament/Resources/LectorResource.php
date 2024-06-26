<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectorResource\Pages;
use App\Filament\Resources\LectorResource\RelationManagers\DiplomasRelationManager;
use App\Filament\Resources\LectorResource\RelationManagers\LectorRatesRelationManager;
use App\Models\Lector;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class LectorResource extends Resource
{
    protected static ?string $model = Lector::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Лекторы';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Лекторы';
    protected static ?string $pluralModelLabel = 'Лекторы';
    protected static ?string $modelLabel = 'Лектор';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = 'Лекции';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\TextInput::make('id')
                                ->label('ID, заполняется автоматически')
                                ->disabled()
                                ->visible(false)->columnSpan(1),
                            Forms\Components\TextInput::make('name')
                                ->label('Имя лектора')
                                ->required()
                                ->maxLength(255)->columnSpan(1),
                            Forms\Components\TextInput::make('position')
                                ->label('Должность, позиция')
                                ->required()
                                ->maxLength(255)->columnSpan(1),
                            Forms\Components\DatePicker::make('career_start')
                                ->label('Начало карьеры')
                                ->required()->columnSpan(1),
                        ])
                        ->columnSpan(1),
                    Forms\Components\FileUpload::make('photo')
                        ->label('Фото лектора')
                        ->directory('images/lectors')
                        ->maxSize(10240)
                        ->image()
                        ->imageResizeMode('force')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('300')
                        ->imageResizeTargetHeight('300'),
                ])->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\RichEditor::make('description')
                        ->label('О лекторе')
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
                        ->required()
                        ->maxLength(65535),
                    Forms\Components\Placeholder::make('avg_rate')
                    ->content(fn(?Model $record) => $record->averageRate?->rating ?? 'Пока нет ни одной оценки')
                    ->label('Средняя оценка, из 10')
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя лектора')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Должность, позиция')
                    ->limit(35)
                    ->tooltip(fn (?Model $record): string => $record->position),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Фото лектора'),
                Tables\Columns\TextColumn::make('career_start')
                    ->label('Начало карьеры')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('averageRate.rating')
                    ->default('пока нет оценок')
                    ->label('Рейтинг, из 10')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
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
            DiplomasRelationManager::class,
            LectorRatesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLectors::route('/'),
            'create' => Pages\CreateLector::route('/create'),
            'edit' => Pages\EditLector::route('/{record}/edit'),
        ];
    }
}
