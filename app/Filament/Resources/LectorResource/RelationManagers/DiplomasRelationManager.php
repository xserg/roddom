<?php

namespace App\Filament\Resources\LectorResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class DiplomasRelationManager extends RelationManager
{
    protected static string $relationship = 'diplomas';
    protected static ?string $inverseRelationship = 'lector';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Дипломы';
    protected static ?string $label = 'Диплом';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('preview_picture')
                ->label('превью диплома')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\ImageColumn::make('preview_picture')
                    ->label('превью диплома'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить')
                    ->disableCreateAnother(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
//                Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\DissociateBulkAction::make(),
//                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
