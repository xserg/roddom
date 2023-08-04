<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
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
use Illuminate\Support\HtmlString;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;
    protected static ?string $navigationIcon = 'heroicon-o-mail-open';
    protected static ?string $navigationLabel = 'Отзывы';
    protected static ?string $modelLabel = 'Отзыв';
    protected static ?string $pluralModelLabel = 'Отзывы';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Placeholder::make('content')
                        ->content(fn () => new HtmlString("<span class='text-sm font-medium leading-4 text-gray-700'>Текст отзыва</span>"))
                        ->disableLabel(),
                    Forms\Components\Card::make([
                        Forms\Components\Placeholder::make('content')
                            ->content(fn (?Feedback $record) => $record->content)
                            ->disableLabel()
                    ]),
                    Forms\Components\Placeholder::make('user_name')
                        ->content(function (Closure $get) {
                            $user = User::firstWhere('id', $get('user_id'));
                            $name = $user?->name ?? $user?->email;
                            $path = UserResource::getUrl('edit', ['record' => $user?->id]);
                            $classes = 'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500';

                            return new HtmlString("<a class=$classes href=\"$path\">$name</a>");
                        })->label('Пользователь, оставивший отзыв'),
                    Forms\Components\Placeholder::make('lecture_title')
                        ->content(function (Closure $get) {
                            $lecture = Lecture::firstWhere('id', $get('lecture_id'));
                            $path = LectureResource::getUrl('edit', ['record' => $lecture?->id]);
                            $classes = 'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500';

                            return new HtmlString("<a class=$classes href=\"$path\">$lecture?->title</a>");
                        })
                        ->label('Лекция'),
                    Forms\Components\Placeholder::make('lector_name')
                        ->content(function (Closure $get) {
                            $lector = Lector::firstWhere('id', $get('lector_id'));
                            $path = LectorResource::getUrl('edit', ['record' => $lector?->id]);
                            $classes = 'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500';

                            return new HtmlString("<a class=$classes href=\"$path\">$lector?->name</a>");
                        })
                        ->label('Лектор'),
                    Forms\Components\Placeholder::make('created_at')
                        ->content(fn (?Feedback $record) => $record->created_at->translatedFormat('j F Y, h:i'))
                        ->label('Отправлен'),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user_name_formatted')
                    ->formatStateUsing(fn (?Feedback $record) => $record->user->name ?? $record->user->email)
                    ->url(fn (?Feedback $record) => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->label('Пользователь')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lecture.title')
                    ->url(fn (?Feedback $record) => LectureResource::getUrl('edit', ['record' => $record->lecture_id]))
                    ->tooltip(fn (?Feedback $record): string => $record->lecture->title)
                    ->label('Лекция')
                    ->limit(35)
                    ->sortable(),
                Tables\Columns\TextColumn::make('lector.name')
                    ->url(fn (?Feedback $record) => LectorResource::getUrl('edit', ['record' => $record->lector_id]))
                    ->label('Лектор')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('j F Y, h:i')
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
                Tables\Actions\DeleteBulkAction::make(),
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
            'view' => Pages\ViewFeedback::route('/{record}'),
        ];
    }
}
