<?php

namespace App\Filament\Resources\SubscriptionResource\RelationManagers;

use App\Filament\Resources\LectureResource;
use App\Models\Lecture;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LecturesRelationManager extends RelationManager
{
    protected static string $relationship = 'lectures';
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $title = 'Лекции, доступные по данной подписке';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('лекция')
                ->url(fn(?Lecture $record) => LectureResource::getUrl('edit', ['record' => $record->id]))
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
