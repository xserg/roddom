<?php

namespace App\Filament\Resources;

use App\Enums\ThreadStatusEnum;
use App\Filament\Resources\ThreadResource\Pages;
use App\Filament\Resources\ThreadResource\RelationManagers\MessagesRelationManager;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\Position;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Threads\Thread;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ThreadResource extends Resource
{
    protected static ?string $model = Thread::class;

    protected static ?string $slug = 'threads';
    protected static ?string $modelLabel = 'Беседа';
    protected static ?string $pluralModelLabel = 'Беседы';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationGroup = 'Беседы';
    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('messages');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Select::make('user_id')
//                    ->options(User::where('is_admin', false)->get()->pluck('name', 'id'))
//                ->label('Юзер'),
                Select::make('status')
                    ->label('Статус беседы')
                    ->disablePlaceholderSelection()
                    ->options(collect(ThreadStatusEnum::cases())->pluck('name', 'value')),
                Card::make([
                    Placeholder::make('user_name')
                        ->content(function (?Thread $record) {
                            $user = $record->participants->firstWhere('opened', true)?->user;

                            if (is_null($user)) {
                                return 'не определен';
                            }
                            $name = $user->name ?? $user->email;
                            $path = UserResource::getUrl('edit', ['record' => $user->id]);
                            $classes = 'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500';

                            return new HtmlString("<a class=$classes href=\"$path\">$name</a>");
                        })->label('Беседа с пользователем'),
                    Placeholder::make('created_at')
                        ->label('Создана')
                        ->content(fn (?Thread $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label('Обновлена')
                        ->content(fn (?Thread $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->visible(fn (?Thread $record) => ! is_null($record))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('С юзером')
                    ->sortable()
                    ->formatStateUsing(fn (?Thread $record) => $record->openedParticipant?->user?->name ?? $record->openedParticipant?->user?->email ?? 'не определен')
                    ->url(function (Thread $record): string {
                        return $record->openedParticipant ?
                            UserResource::getUrl('edit', ['record' => $record->openedParticipant->user->id]) :
                            '';
                    }),
                TextColumn::make('updated_at')
                    ->label('обновлена')
                    ->dateTime(),
                BadgeColumn::make('status')
                    ->label('Статус беседы'),
                TextColumn::make('messages_count')
                    ->label('Количество сообщений'),
                BadgeColumn::make('unread')
                    ->formatStateUsing(fn (?Thread $record): string => ($record->updated_at > $record->participantForUser(auth()->id())?->read_at) && $record->messages_count > 0 ? 'есть' : 'отсутствуют')
                    ->color(static function (?Thread $record): string {
                        if (($record->updated_at > $record->participantForUser(auth()->id())?->read_at) && $record->messages_count > 0) {
                            return 'success';
                        }

                        return 'secondary';
                    })
                    ->label('Непрочитанные сообщения')
            ])
            ->filters([
                Filter::make('Октрытые')
                    ->query(fn (Builder $query): Builder => $query->orWhere('status', ThreadStatusEnum::OPEN))
                    ->label('Октрытые')
                    ->default(),
                Filter::make('Закрытые')
                    ->query(fn (Builder $query): Builder => $query->orWhere('status', ThreadStatusEnum::CLOSED))
                    ->label('Закрытые'),
//                Filter::make('Есть непрочитанные сообщения')
//                    ->query(function (Builder $query): Builder {
//                        return $query->whereHas('participants', function (Builder $query) {
//                            //take only threads, where thread's updated_at > user's read_at
//                            return $query->where('read_at', '<', )
//                        });
//                    })
//                    ->label('Есть непрочитанные сообщения'),
            ])
            ->actions([
                Action::make('to-msgs')->label('К сообщениям')
                    ->url(fn (?Model $record) => ThreadResource::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0]))
            ])
            ->actionsPosition(Position::BeforeCells)
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        $threads = static::getModel()::all();

        //compare users read_at and thread read_at, count it
        $count = 0;
        $threads->each(function (Thread $thread) use (&$count) {
            $adminIsNotParticipant = is_null($thread->participantForUser(auth()->id()));
            $threadUpdatedLaterThanAdminsReadAt = $thread->updated_at > $thread->participantForUser(auth()->id())?->read_at;

            if ($adminIsNotParticipant || $threadUpdatedLaterThanAdminsReadAt) {
                $count++;
            }
        });

        if ($count > 0) {
            return '+' . $count;
        }

        return null;
    }

    protected static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThreads::route('/'),
            'create' => Pages\CreateThread::route('/create'),
            'edit' => Pages\EditThread::route('/{record}/edit'),
        ];
    }
}
