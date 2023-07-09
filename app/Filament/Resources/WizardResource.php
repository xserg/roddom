<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WizardResource\Pages;
use App\Filament\Resources\WizardResource\RelationManagers;
use App\Models\Wizard as WizardModel;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;

class WizardResource extends Resource
{
    protected static ?string $model = WizardModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Форма «Мой план родов»';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Форма «Мой план родов»';
    protected static ?string $navigationGroup = 'Форма «Мой план родов»';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $pluralModelLabel = 'Шаги формы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('order')
                    ->required()
                    ->label('порядковый номер')
                    ->columnSpan(2),
                TextInput::make('title')
                    ->required()
                    ->label('наименование')
                    ->columnSpan(2),
                Builder::make('form')
                    ->label(fn (?Model $record) => $record?->order ? "шаг {$record->order}" : "шаг формы")
                    ->columnSpan(2)
                    ->blocks([
                        Builder\Block::make('question-type-checkbox')
                            ->label('несколько вариантов ответа/checkboxes')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Builder::make('answers')
                                    ->label('ответы')
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('ответ')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст поля')
                                            ]),
                                    ])
                            ]),
                        Builder\Block::make('question-type-radio')
                            ->label('один вариант ответа/radiobuttons')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Builder::make('answers')
                                    ->label('ответы')
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('ответ')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст поля')
                                            ]),
                                    ])
                            ]),
                        Builder\Block::make('question-type-one-text-field')
                            ->label('одно текстовое поле')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Forms\Components\TextInput::make('field-text-1')
                                    ->label('текст поля 1'),
                            ]),
                        Builder\Block::make('question-type-two-text-field')
                            ->label('два текстовых поля')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Forms\Components\TextInput::make('field-text-1')
                                    ->label('текст поля 1'),
                                Forms\Components\TextInput::make('field-text-2')
                                    ->label('текст поля 2'),
                            ]),
                        Builder\Block::make('question-type-three-text-field')
                            ->label('три текстовых поля')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Forms\Components\TextInput::make('field-text-1')
                                    ->label('текст поля 1'),
                                Forms\Components\TextInput::make('field-text-2')
                                    ->label('текст поля 2'),
                                Forms\Components\TextInput::make('field-text-3')
                                    ->label('текст поля 3'),
                            ]),
                        Builder\Block::make('question-type-one-number-field')
                            ->label('одно числовое поле')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Forms\Components\TextInput::make('field-text-1')
                                    ->label('текст поля 1')
                                    ->integer(),
                            ]),
                        Builder\Block::make('textarea')
                            ->label('просто текст/textarea')
                            ->schema([
                                Forms\Components\RichEditor::make('text')
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
                                    ->label('textarea')
                            ]),
                    ])->collapsible()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('порядковый номер'),
                Tables\Columns\TextColumn::make('title')
                    ->label('наименование'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWizards::route('/'),
        ];
    }
}
