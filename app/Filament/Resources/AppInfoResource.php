<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppInfoResource\Pages;
use App\Filament\Resources\AppInfoResource\RelationManagers;
use App\Models\AppInfo;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppInfoResource extends Resource
{
    protected static ?string $model = AppInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Динамические заголовки';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Приложение';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('agreement_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('agreement_text')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('recommended_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('recommended_subtitle')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('lectures_catalog_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('lectures_catalog_subtitle')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('out_lectors_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('not_viewed_yet_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('more_in_the_collection')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('about_lector_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('diplomas_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('lectors_videos')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('about_app')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('app_author_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_link_share_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_link_share_link')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_show_qr_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_show_qr_link')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agreement_title'),
                Tables\Columns\TextColumn::make('agreement_text'),
                Tables\Columns\TextColumn::make('recommended_title'),
                Tables\Columns\TextColumn::make('recommended_subtitle'),
                Tables\Columns\TextColumn::make('lectures_catalog_title'),
                Tables\Columns\TextColumn::make('lectures_catalog_subtitle'),
                Tables\Columns\TextColumn::make('out_lectors_title'),
                Tables\Columns\TextColumn::make('not_viewed_yet_title'),
                Tables\Columns\TextColumn::make('more_in_the_collection'),
                Tables\Columns\TextColumn::make('about_lector_title'),
                Tables\Columns\TextColumn::make('diplomas_title'),
                Tables\Columns\TextColumn::make('lectors_videos'),
                Tables\Columns\TextColumn::make('app_title'),
                Tables\Columns\TextColumn::make('about_app'),
                Tables\Columns\TextColumn::make('app_author_name'),
                Tables\Columns\TextColumn::make('app_link_share_title'),
                Tables\Columns\TextColumn::make('app_link_share_link'),
                Tables\Columns\TextColumn::make('app_show_qr_title'),
                Tables\Columns\TextColumn::make('app_show_qr_link'),
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
            'index' => Pages\ListAppInfos::route('/'),
            'create' => Pages\CreateAppInfo::route('/create'),
            'edit' => Pages\EditAppInfo::route('/{record}/edit'),
        ];
    }
}
