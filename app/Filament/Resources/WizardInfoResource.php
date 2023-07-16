<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WizardInfoResource\Pages;
use App\Filament\Resources\WizardInfoResource\RelationManagers;
use App\Models\Wizard as WizardModel;
use App\Models\WizardInfo;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WizardInfoResource extends Resource
{
    protected static ?string $model = WizardInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Общие поля';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Общие поля';
    protected static ?string $navigationGroup = 'Форма «Мой план родов»';
    protected static ?string $recordTitleAttribute = 'readable_key';
    protected static ?string $pluralModelLabel = 'Общие поля';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('value')
                    ->required()
                    ->label('значение поля')
                    ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('readable_key')
                    ->label('имя поля'),
                Tables\Columns\TextColumn::make('value')
                    ->label('значение поля'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWizardInfos::route('/'),
        ];
    }
}
