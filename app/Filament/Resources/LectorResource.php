<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectorResource\Pages;
use App\Filament\Resources\LectorResource\RelationManagers\DiplomasRelationManager;
use App\Models\Lector;
use App\Models\SubcategoryPrices;
use App\Services\CategoryService;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;

class LectorResource extends Resource
{
    protected static ?string $model = Lector::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Лекторы';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Лекторы';
    protected static ?string $pluralModelLabel = 'Лекторы';
    protected static ?string $modelLabel = 'Лектор';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('id')
                        ->label('ID, заполняется автоматически')
                        ->disabled()
                        ->visible(false),
                    Forms\Components\TextInput::make('name')
                        ->label('Имя лектора')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('position')
                        ->label('Должность, позиция')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('career_start')
                        ->label('Начало карьеры')
                        ->required(),
                ])->columns(1),
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
                            'preview'
                        ])
                        ->required()
                        ->maxLength(65535),
                    Forms\Components\FileUpload::make('photo')
                        ->label('Фото лектора')
                        ->directory('images/lectors')
                        ->maxSize(10240)
                        ->image()
                        ->imageResizeMode('force')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('300')
                        ->imageResizeTargetHeight('300'),
                ])->columns(2)
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
                    ->tooltip(fn(?Model $record): string => $record->position),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Фото лектора'),
//                Tables\Columns\TextColumn::make('career_start')
//                    ->label('Начало карьеры')
//                    ->sortable(),
                Tables\Columns\TextColumn::make('rate_avg')
                    ->getStateUsing(
                        function (?Lector $record): ?string {
                            return round($record->rates['rate_avg'], 1) ?: 'нет оценок';
                        }
                    )
                    ->label('Рейтинг, из 10'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // ...
//                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
//                Tables\Actions\DissociateBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DiplomasRelationManager::class
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
