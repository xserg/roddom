<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Feedback;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $label = 'Подписки';
    protected static ?string $pluralModelLabel = 'Подписки';
    protected static ?string $modelLabel = 'Подписка';
    protected static ?string $navigationGroup = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->multiple()
                    ->options(function () {
                        $users = User::select(['name', 'email', 'id'])->get();
                        $options = [];
                        foreach ($users as $user) {
                            $options[$user->id] = $user->name ?? $user->email;
                        }
                        return $options;
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        $usersByNames = User::where('name', 'like', "%$search%")
                            ->limit(10)
                            ->get();
                        if ($usersByNames->isNotEmpty()) {
                            return $usersByNames->pluck('name', 'id');
                        }

                        $usersByEmails = User::orWhere('email', 'like', "%$search%")
                            ->limit(10)
                            ->get();
                        if ($usersByEmails->isNotEmpty()) {
                            return $usersByEmails->pluck('email', 'id');
                        }
                    })
                    ->disabled(function (string $context) {
                        return $context === 'edit';
                    })
                    ->label('пользователь'),
                Forms\Components\Select::make('period_id')
                    ->relationship('period', 'length')
                    ->label('период подписки, дней')
                    ->afterStateUpdated(function (Closure $set, string $context, $state, Closure $get) {
                        $periodLength = Period::query()->firstWhere('id', $state)->length;

                        if ($context === 'create') {
                            $set('start_date', now()->toDateTimeString());
                            $set('end_date', now()->addDays($periodLength)->toDateTimeString());
                        } elseif ($context === 'edit') {
                            $set('end_date', Carbon::createFromDate($get('start_date'))->addDays($periodLength)->toDateTimeString());
                        }
                    })
                    ->reactive()
                    ->required(),
                Forms\Components\Select::make('subscriptionable_type')
                    ->required()
                    ->label('подписка на')
                    ->options([
                        Lecture::class => 'Лекция',
                        Category::class => 'Категория',
                        Promo::class => 'Промопак лекций',
                        EverythingPack::class => 'Все лекции',
                    ])
                    ->afterStateUpdated(function (Closure $set, Forms\Components\Select $component) {
                        if (
                            $component->getState() === Promo::class ||
                            $component->getState() === EverythingPack::class
                        ) {
                            $set('subscriptionable_id', 1);
                        } else {
                            $set('subscriptionable_id', null);
                        }
                    })
                    ->reactive(),
                Forms\Components\Select::make('subscriptionable_id')
                    ->label('id объекта подписки')
                    ->options(function (Closure $set, Closure $get) {
                        $type = $get('subscriptionable_type');
                        return match ($type) {
                            Category::class => Category::orderBy('title')->pluck('title', 'id'),
                            Lecture::class => Lecture::orderBy('title')->pluck('title', 'id'),
                            Promo::class => Promo::all()->pluck('id', 'id'),
                            EverythingPack::class => [1 => 1],
                            default => null
                        };
                    })
//                    ->multiple(function (Closure $get) {
//                        $type = $get('subscriptionable_type');
//                        if ($type === Lecture::class ||
//                            $type === Category::class) {
//                            return true;
//                        }
//                        return false;
//                    })
                    ->optionsLimit(0)
//                    ->disabled(fn (Closure $get) => is_null($get('subscriptionable_type')) ||
//                        $get('subscriptionable_type') === Promo::class ||
//                        $get('subscriptionable_type') === EverythingPack::class)
                    ->visible(fn (Closure $get) =>
                        $get('subscriptionable_type') === Lecture::class ||
                        $get('subscriptionable_type') === Category::class
                    )
                    ->saveRelationshipsWhenHidden()
                    ->required(),
                Forms\Components\DateTimePicker::make('start_date')
//                    ->afterStateHydrated(function ($state, Component $component) {
//                        if (is_null($state)) {
//                            $component->state(Carbon::now());
//                        }
//                    })
                    ->label('начало подписки')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('окончание подписки')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->sortable()
                    ->url(function (Subscription $record): string {
                        $route = route('filament.resources.users.edit', ['record' => $record->user_id]);
                        return $route;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('entity_title')
                    ->label('подписка на')
                    ->limit(25)
                    ->toggleable()
                    ->tooltip(fn (?Model $record): ?string => $record?->entity_title),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.phone')->label('номер телефона')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('period_id')
                    ->formatStateUsing(
                        fn (?Model $record): string => $record?->period->length
                    )
                    ->label('период покупки, дней')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('цена подписки')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('начало подписки')
                    ->toggleable()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('конец подписки')
                    ->toggleable()
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([//
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
