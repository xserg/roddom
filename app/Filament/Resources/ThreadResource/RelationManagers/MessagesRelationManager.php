<?php

namespace App\Filament\Resources\ThreadResource\RelationManagers;

use App\Filament\Resources\UserResource;
use App\Models\Threads\Message;
use App\Models\Threads\Thread;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $modelLabel = 'Сообщение';
    protected static ?string $pluralModelLabel = 'Сообщения';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->label('сообщение')
                    ->required()
                    ->maxLength(1024),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->limit(15)
                    ->formatStateUsing(fn (?Message $record) => $record->author->name ?? $record->author->email)
                    ->url(function (Message $record): string {
                        if ($record->author->isAdmin()) {
                            return false;
                        }
                        return UserResource::getUrl('edit', ['record' => $record->author_id]);
                    }),
                Tables\Columns\TextColumn::make('message')->label('Текст сообщения')
                    ->limit(75)
                    ->tooltip(fn (?Message $record): string => $record->message),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->limit(10)
                    ->formatStateUsing(fn (?string $state) => Carbon::parse($state)->diffForHumans()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    $data['author_id'] = auth()->id();

                    return $data;
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
