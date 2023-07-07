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
                Builder::make('form')
                    ->label(fn (?Model $record) => $record ? "шаг {$record->id}" : "шаг формы")
                    ->blocks([
                        Builder\Block::make('question-type-1')
                            ->label('вопрос с несколькими вариантами ответа')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),

                                Builder::make('answers')
                                    ->label('ответы')
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('ответ')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст ответа')
                                            ]),
                                    ])
                            ]),
                        Builder\Block::make('question-type-2')
                            ->label('вопрос с одним вариантом ответа')
                            ->schema([
                                TextInput::make('text')
                                    ->label('текст вопроса')
                                    ->required(),

                                Builder::make('answers')
                                    ->label('ответы')
                                    ->blocks([
                                        Builder\Block::make('answer')
                                            ->label('ответ')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('текст ответа')
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
                Tables\Columns\TextColumn::make('id'),
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
