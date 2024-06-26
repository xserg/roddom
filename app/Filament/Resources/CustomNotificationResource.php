<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomNotificationResource\Pages;
use App\Models\CustomNotification;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Model;

class CustomNotificationResource extends Resource
{
    protected static ?string $model = CustomNotification::class;

    protected static ?string $navigationLabel = 'Уведомления';
    protected static ?string $navigationGroup = 'Уведомления и обращения';
    protected static ?string $modelLabel = 'Уведомления';
    protected static ?string $pluralModelLabel = 'Уведомления';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    TiptapEditor::make('text')
                        ->profile('barebone')
                        ->output(TiptapEditor::OUTPUT_HTML),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('text')
                    ->formatStateUsing(fn (string $state) => strip_tags($state))
                    ->label('Текст уведомления')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->toggleable()
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('send-notification')
                    ->label('Послать уведомление')
                    ->action(function () {
                        User::all()->each(fn (User $user) => $user->markNotificationUnread());
                    })
                    ->visible(fn (?Model $record) => CustomNotification::latest()->value('id') === $record->id),
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
            'index' => Pages\ManageCustomNotifications::route('/'),
        ];
    }
}
