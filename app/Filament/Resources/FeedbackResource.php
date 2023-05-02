<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Filament\Resources\FeedbackResource\RelationManagers;
use App\Models\Feedback;
use App\Models\Lector;
use App\Models\Lecture;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Position;
use Illuminate\Database\Eloquent\Model;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Отзывы';
    protected static ?string $label = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('user_id')
                        ->required(),
                    Forms\Components\TextInput::make('lecture_id')
                        ->required(),
                    Forms\Components\TextInput::make('lector_id')
                        ->required(),
                ])->columns(3)->hidden(),
                Forms\Components\Card::make([
                    Forms\Components\Textarea::make('content')
                        ->required()
                        ->maxLength(65535)
                        ->label('отзыв'),
                    Forms\Components\TextInput::make('user_name')
                        ->formatStateUsing(function (Closure $get) {
                            return User::firstWhere('id', $get('user_id'))->name;
                        })
                        ->hint(function (Feedback $record): string {
                            $route = route('filament.resources.users.edit', ['record' => $record->user_id]);
                            return $route;
                        })
                        ->label('Пользователь, оставивший отзыв'),
                    Forms\Components\TextInput::make('lecture_title')
                        ->formatStateUsing(function (Closure $get) {
                            return Lecture::firstWhere('id', $get('lecture_id'))->title;
                        })

                        ->hint(function (Feedback $record): string {
                            $route = route('filament.resources.lectures.edit', ['record' => $record->lecture_id]);
                            return $route;
                        })
                        ->label('Лекция'),
                    Forms\Components\TextInput::make('lector_name')
                        ->formatStateUsing(function (Closure $get) {
                            return Lector::firstWhere('id', $get('lector_id'))->name;
                        })
                        ->hint(function (Feedback $record): string {
                            $route = route('filament.resources.lectors.edit', ['record' => $record->lector_id]);
                            return url($route);
                        })
                        ->label('Лектор'),
//                    Forms\Components\TextInput::make('lecture_id')
//                        ->required(),
//                    Forms\Components\TextInput::make('lector_id')
//                        ->required(),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('id')
//                    ->label('id отзыва')
//                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->url(function (Feedback $record): string {
                        $route = route('filament.resources.users.edit', ['record' => $record->user_id]);
                        return $route;
                    })
                    ->label('имя пользователя')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('email пользователя')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lecture.title')
                    ->label('лекция')
                    ->sortable()
                    ->url(function (Feedback $record): string {
                        $route = route('filament.resources.lectures.edit', ['record' => $record->lecture_id]);
                        return $route;
                    }),
                Tables\Columns\TextColumn::make('lector.name')
                    ->label('лектор')
                    ->sortable()
                    ->url(function (Feedback $record): string {
                        $route = route('filament.resources.lectors.edit', ['record' => $record->lector_id]);
                        return $route;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('создано')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->actionsPosition(Position::BeforeCells)
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
//            'create' => Pages\CreateFeedback::route('/create'),
            'view' => Pages\ViewFeedback::route('/{record}'),
//            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
