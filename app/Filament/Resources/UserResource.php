<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('birthdate'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\Toggle::make('is_mother')
                    ->required(),
                Forms\Components\DatePicker::make('pregnancy_start'),
                Forms\Components\DatePicker::make('baby_born'),
                Forms\Components\TextInput::make('photo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('photo_small')
                    ->maxLength(255),
                Forms\Components\Toggle::make('to_delete')
                    ->required(),
                Forms\Components\DateTimePicker::make('next_free_lecture_available'),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_admin')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('birthdate')
                    ->date(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\IconColumn::make('is_mother')
                    ->boolean(),
                Tables\Columns\TextColumn::make('pregnancy_start')
                    ->date(),
                Tables\Columns\TextColumn::make('baby_born')
                    ->date(),
                Tables\Columns\TextColumn::make('photo'),
                Tables\Columns\TextColumn::make('photo_small'),
                Tables\Columns\IconColumn::make('to_delete')
                    ->boolean(),
                Tables\Columns\TextColumn::make('next_free_lecture_available')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                FilamentExportHeaderAction::make('export')
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('Export'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
