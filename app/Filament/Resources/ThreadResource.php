<?php

namespace App\Filament\Resources;

use App\Enums\ThreadStatusEnum;
use App\Filament\Resources\ThreadResource\Pages;
use App\Filament\Resources\ThreadResource\RelationManagers\MessagesRelationManager;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
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


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Select::make('user_id')
//                    ->options(User::where('is_admin', false)->get()->pluck('name', 'id'))
//                ->label('Юзер'),
                Select::make('status')
                    ->label('Статус беседы')
                    ->options(collect(ThreadStatusEnum::cases())->pluck('name', 'value')),
                Card::make([
                    Placeholder::make('user_name')
                        ->content(function (\Closure $get) {
                            $user = User::firstWhere('id', $get('user_id'));
                            $name = $user?->name ?? $user?->email;
                            $path = UserResource::getUrl('edit', ['record' => $user?->id]);
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
                    ->formatStateUsing(fn (?Thread $record) => $record->user->name ?? $record->user->email)
                    ->url(function (Thread $record): string {
                        return UserResource::getUrl('edit', ['record' => $record->user_id]);
                    }),
                TextColumn::make('updated_at')
                    ->label('обновлена')
                    ->dateTime(),
                BadgeColumn::make('status')
                    ->label('Статус беседы')
            ])
            ->filters([
                Filter::make('Октрытые')
                    ->query(fn (Builder $query): Builder => $query->orWhere('status', ThreadStatusEnum::OPEN))
                    ->label('Октрытые')
                    ->default(),
                Filter::make('Закрытые')
                    ->query(fn (Builder $query): Builder => $query->orWhere('status', ThreadStatusEnum::CLOSED))
                    ->label('Закрытые'),
            ])
            ->actions([
                EditAction::make()->label('К сообщениям')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThreads::route('/'),
            'create' => Pages\CreateThread::route('/create'),
            'edit' => Pages\EditThread::route('/{record}/edit'),
        ];
    }
}
