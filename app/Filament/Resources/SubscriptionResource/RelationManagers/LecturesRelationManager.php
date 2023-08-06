<?php

namespace App\Filament\Resources\SubscriptionResource\RelationManagers;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\LectureResource;
use App\Models\Lecture;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;

class LecturesRelationManager extends RelationManager
{
    protected static string $relationship = 'lectures';
    protected static ?string $inverseRelationship = 'subscriptionItems';
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
                    ->limit(50)
                    ->tooltip(fn(?Lecture $record) => $record->title)
                    ->url(fn (?Lecture $record) => LectureResource::getUrl('edit', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('категория')
                    ->url(fn (?Lecture $record) => CategoryResource::getUrl('edit', ['record' => $record->category->id])),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (AttachAction $action, Livewire $livewire): array => [
                        Forms\Components\Select::make('recordId')
                            ->label('лекция')
                            ->options(function () use ($livewire) {
                                $currentSubscription = $livewire->ownerRecord;
                                $lectures = Lecture::whereDoesntHave(
                                    'subscriptionItems',
                                    fn (Builder $query) => $query->where('id', $currentSubscription->id))
                                    ->with(array('category'))
                                    ->get();
                                $options = [];
                                $lectures->each(function (Lecture $lecture) use (&$options) {
                                    $lectureTitle = $lecture->title;
                                    $categoryTitle = $lecture->category->title;

                                    $options[$lecture->id] = Str::limit($lectureTitle, 40) . ' (' . Str::limit($categoryTitle, 25) . ')';
                                });
                                return $options;
                            })
                    ])
                    ->preloadRecordSelect()
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
