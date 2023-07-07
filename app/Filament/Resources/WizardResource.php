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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->columnSpan(2),
                Builder::make('form')
                    ->label(fn (?Model $record) => $record ? "шаг {$record->id}" : "шаг формы")
                    ->blocks([
                        Builder\Block::make('question-type-checkbox')
                            ->label('несколько вариантов ответа')
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
                            ->label('один вариант ответа')
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

                                Builder::make('answers')
                                    ->label('поля')
                                    ->maxItems(1)
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('поле')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст поля')
                                            ]),
                                    ])
                            ]),
                        Builder\Block::make('question-type-two-text-field')
                            ->label('два текстовых поля')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Builder::make('answers')
                                    ->label('поля')
                                    ->maxItems(2)
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('поле')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст поля')
                                            ]),
                                    ])
                            ]),
                        Builder\Block::make('question-type-three-text-field')
                            ->label('три текстовых поля')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Builder::make('answers')
                                    ->label('поля')
                                    ->maxItems(3)
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('поле')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст поля'),
                                            ]),
                                    ])
                            ]),
                        Builder\Block::make('question-type-one-number-field')
                            ->label('одно числовое поле')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('текст описания'),

                                Builder::make('answers')
                                    ->label('поля')
                                    ->maxItems(1)
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('поле')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст поля'),
                                            ]),
                                    ])
                            ]),
                    ])->collapsible()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
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
