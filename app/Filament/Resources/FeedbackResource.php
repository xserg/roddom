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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Expr\AssignOp\Mod;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Отзывы';

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
                ])->columns(3),
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('user_name')
                        ->formatStateUsing(function (Closure $get) {
                            return User::firstWhere('id', $get('user_id'))->name;
                        }),
                    Forms\Components\TextInput::make('lecture_title')
                        ->formatStateUsing(function (Closure $get) {
                            return Lecture::firstWhere('id', $get('lecture_id'))->title;
                        }),
                    Forms\Components\TextInput::make('lector_name')
                        ->formatStateUsing(function (Closure $get) {
                            return Lector::firstWhere('id', $get('lector_id'))->name;
                        }),
//                    Forms\Components\TextInput::make('lecture_id')
//                        ->required(),
//                    Forms\Components\TextInput::make('lector_id')
//                        ->required(),
                ])->columns(3),

                Forms\Components\Textarea::make('content')
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('id отзыва')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->url(function (Feedback $record): string {
                        $route = route('filament.resources.users.edit', ['record' => $record->user_id]);
                        return $route;
                    })
                    ->label('имя пользователя')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lecture.title')
                    ->label('лекция')
                    ->url(function (Feedback $record): string {
                        $route = route('filament.resources.lectures.edit', ['record' => $record->lecture_id]);
                        return $route;
                    }),
                Tables\Columns\TextColumn::make('lector.name')
                    ->label('лектор')
                    ->url(function (Feedback $record): string {
                        $route = route('filament.resources.lectors.edit', ['record' => $record->lector_id]);
                        return $route;
                    }),
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
            ->bulkActions([
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
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'view' => Pages\ViewFeedback::route('/{record}'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
