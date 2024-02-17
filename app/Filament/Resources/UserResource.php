<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\RefTypeEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\DevicesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReferralsMadePaymentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReferralsPaymentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\WatchedLecturesHistoryRelationManager;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Promo;
use App\Models\RefPoints;
use App\Models\Subscription;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Webbingbrasil\FilamentCopyActions\Forms\Actions\CopyAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Пользователи';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Пользователи';
    protected static ?string $pluralModelLabel = 'Пользователи';
    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_admin', 0)
            ->with([
                'referrals',
                'referralsSecondLevel',
                'referralsThirdLevel',
                'referralsFourthLevel',
                'referralsFifthLevel',
                'watchedLecturesHistory',
                'watchedLecturesToday',
                'watchedLectures',
                'watchedLecturesHistoryToday',
                'participants.thread'
            ]);
    }

    public static function form(Form $form): Form
    {
        $uuid = Str::uuid();

        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->maxLength(255)
                        ->label('Имя'),
                    Forms\Components\FileUpload::make('photo')
                        ->directory('images/users')
                        ->label('Фото пользователя')
                        ->maxSize(10240)
                        ->image()
                        ->imageResizeMode('force')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('300')
                        ->imageResizeTargetHeight('300'),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(table: User::class, ignoreRecord: true),
                    Forms\Components\DatePicker::make('birthdate')->label('Дата рождения пользователя'),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->label('Номер телефона'),
                    Forms\Components\Toggle::make('is_mother')
                        ->required()
                        ->label('Родился ли ребёнок'),
                    Forms\Components\DatePicker::make('pregnancy_start')
                        ->label('Начало беременности'),
                    Forms\Components\DatePicker::make('baby_born')
                        ->label('Дата рождения ребёнка'),
                    Forms\Components\DateTimePicker::make('next_free_lecture_available')
                        ->label('Дата, когда можно смотреть бесплатную лекцию'),
                    Forms\Components\DateTimePicker::make('created_at')
                        ->label('Дата создания профиля'),
                    Forms\Components\DateTimePicker::make('profile_fulfilled_at')
                        ->label('Дата заполнения профиля'),
                ])->columns(2),

                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->confirmed()
                        ->required()
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->visible(fn (string $context): bool => $context === 'create')
                        ->label('Пароль'),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->password()
                        ->required()
                        ->label('Подтверждение пароля')
                        ->visible(fn (string $context): bool => $context === 'create')
                ])
                    ->visible(fn (string $context): bool => $context === 'create')
                    ->columns(2),


                Forms\Components\Card::make([
                    Forms\Components\Select::make('referrer_id')
                        ->options(function (string $context, ?Model $record) {
                            if ($context === 'edit') {
                                $allLevelsReferralsAndSelfIds = [
                                    ...$record->referrals->pluck('id')->toArray(),
                                    ...$record->referralsSecondLevel->pluck('id')->toArray(),
                                    ...$record->referralsThirdLevel->pluck('id')->toArray(),
                                    ...$record->referralsFourthLevel->pluck('id')->toArray(),
                                    ...$record->referralsFifthLevel->pluck('id')->toArray(),
                                    $record->id
                                ];
                                $users = User::select(['name', 'email', 'id'])
                                    ->where('is_admin', 0)
                                    ->whereNotIn('id', $allLevelsReferralsAndSelfIds)
                                    ->get();
                            } elseif ($context === 'create') {
                                $users = User::select(['name', 'email', 'id'])
                                    ->where('is_admin', 0)
                                    ->get();
                            }

                            $options = [];
                            foreach ($users as $user) {
                                $options[$user->id] = $user->name ?? $user->email;
                            }
                            return $options;
                        })
                        ->label('Реферер'),

                    Forms\Components\Select::make('ref_type')
                        ->options(function (string $context) {
                            return [
                                RefTypeEnum::VERTICAL->value => 'вертикальный',
                                RefTypeEnum::HORIZONTAL->value => 'горизонтальный'
                            ];
                        })
                        ->default(RefTypeEnum::VERTICAL->value)
                        ->required()
                        ->disablePlaceholderSelection()
                        ->label('Тип'),

                    Forms\Components\Placeholder::make('referer_link')
                        ->content(function (?Model $record) {
                            $name = $record->referrer?->name ?? $record->referrer?->email;
                            $classes = 'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500';
                            $href = UserResource::getUrl('edit', ['record' => $record->referrer?->id]);
                            return new HtmlString("<a class=$classes href=\"$href\">$name</a>");
                        })
                        ->label('Страница реферера')
                        ->visible(fn (string $context, ?Model $record) => $context === 'edit' && $record->hasReferrer()),

                    Forms\Components\Placeholder::make('descendants_count')
                        ->label('Количество рефералов')
                        ->content(function (?Model $record) {
                            $allLevelsReferralsCount =
                                $record->referrals->count() +
                                $record->referralsSecondLevel->count() +
                                $record->referralsThirdLevel->count() +
                                $record->referralsFourthLevel->count() +
                                $record->referralsFifthLevel->count();

                            return $allLevelsReferralsCount;
                        })
                        ->columnSpan(2)
                        ->visible(fn (string $context) => $context === 'edit'),
//                    Forms\Components\Placeholder::make('ref_link')
//                        ->label('Реф ссылка')
//                        ->content(function (?Model $record) {
//                            return config('app.frontend_url') . "/register?ref=" . $record->ref_token;
//                        })
//                        ->columnSpan(2)
//                        ->visible(fn (string $context) => $context === 'edit'),
                    Forms\Components\TextInput::make('ref_link')
                        ->label('Реф ссылка')
                        ->formatStateUsing(function (?Model $record) {
                            return config('app.frontend_url') . "/register?ref=" . $record?->ref_token;
                        })
                        ->disabled()
                        ->prefixAction(CopyAction::make())
                        ->columnSpan(2)
                        ->visible(fn (string $context) => $context === 'edit'),
                ])->columns(2)->columnSpan(1),

                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('points')
                        ->afterStateHydrated(function (?RefPoints $record, TextInput $component) {
                            if (is_null($record?->points)) {
                                $component->state(0);
                            }
                            $component->state(number_format($record?->points / 100, 2, thousands_separator: ''));
                        })
                        ->dehydrateStateUsing(fn ($state) => $state * 100)
                        ->mask(fn (TextInput\Mask $mask) => $mask
                            ->numeric()
                            ->decimalPlaces(2)
                            ->decimalSeparator('.')
                        )
                        ->numeric()
                        ->minValue(0)
                        ->nullable()
                        ->label('Бебикоины'),
                ])
                    ->relationship('refPoints')
                    ->columnSpan(1),

                /*
                 * SUBSCRIPTIONS - REPEATER
                 */

                Forms\Components\Section::make(fn (?Model $record) => 'Подписки (активных: ' . $record?->actualSubscriptions()->count() . ', всего: ' . $record?->subscriptions()->count() . ')')
                    ->visible(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Repeater::make('subscriptions')
                                    ->relationship('subscriptions')
                                    ->label('Подписки')
                                    ->grid(2)
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->createItemButtonLabel('Добавить подписку')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Select::make('subscriptionable_type')
                                            ->required()
                                            ->disableLabel()
                                            ->placeholder('тип подписки')
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
                                            ->reactive()
                                            ->columnSpan(2),
                                        Forms\Components\Select::make('subscriptionable_id')
                                            ->placeholder('объект подписки')
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
                                            ->disabled(fn (Closure $get) => is_null($get('subscriptionable_type'))
                                                || $get('subscriptionable_type') === Promo::class
                                                || $get('subscriptionable_type') === EverythingPack::class)
                                            ->dehydrated()
                                            ->required()
                                            ->disableLabel()
                                            ->columnSpan(2),
                                        Forms\Components\DateTimePicker::make('start_date')
                                            ->placeholder('начало подписки')
                                            ->label('начало')
                                            ->required()
                                            ->columnSpan(1)
                                            ->afterStateHydrated(fn (Forms\Components\DateTimePicker $component) => $component->getState() ?? $component->state(now()->toDateTimeString())),
                                        Forms\Components\DateTimePicker::make('end_date')
                                            ->placeholder('окончание подписки')
                                            ->label('окончание')
                                            ->required()
                                            ->columnSpan(1),

//                                        Forms\Components\Placeholder::make('link')
//                                            ->visible()
//                                            ->disableLabel()
//                                            ->content(function (?Subscription $record) {
//                                                $text = $record->created_at;
//                                                $url = SubscriptionResource::getUrl('edit', ['record' => $record->id]);
//                                                $link = new HtmlString("<a class='text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500' href='$url'>ссылка</a>");
//                                                return $link;
//                                            }),
                                        Forms\Components\Placeholder::make('is_actual')
                                            ->visible(fn (?Subscription $record) => $record)
                                            ->disableLabel()
                                            ->content(function (?Subscription $record) {
                                                $url = SubscriptionResource::getUrl('edit', ['record' => $record->id]);
                                                $actual = new HtmlString("<a style='color: rgb(34 197 94);' class='transition hover:underline focus:underline' href='$url'>ссылка</a>");
                                                $notActual = new HtmlString("<a style='color: rgb(248 113 113);' class='transition hover:underline focus:underline' href='$url'>ссылка</a>");
                                                return $record->isActual() ? $actual : $notActual;
                                            }),
                                    ])
                                    ->visible(fn (string $context) => $context === 'edit')
                                    ->itemLabel(function (array $state) {
                                        $subscription = isset($state['id']) ? Subscription::find($state['id']) : null;
                                        $status = $subscription?->isActual() ? 'активна' : 'истекла';
                                        return $subscription ? '#' . $state['id'] . ' - ' . $status : null;
                                    })
                            ])
                    ])
                    ->collapsible()
                    ->collapsed()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('имя')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->label('email')->sortable()->searchable(),
                Tables\Columns\ImageColumn::make('photo')->label('фото')->toggleable(),
                Tables\Columns\TextColumn::make('birthdate')
                    ->date()
                    ->label('дата рождения')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')->label('телефон')->toggleable(),
                Tables\Columns\TextColumn::make('refPoints.points')
                    ->sortable()
                    ->label('бебикоины')
                    ->toggleable()
                    ->formatStateUsing(fn (?string $state) => number_format($state / 100, 2, thousands_separator: '')),
                Tables\Columns\IconColumn::make('is_mother')->label('родился ли ребёнок')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('pregnancy_start')->label('дата начала беременности')->sortable()->date()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('дата создания')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('profile_fulfilled_at')->label('дата заполнения профиля')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('referrer.name')->label('реферер')->toggleable(),
//                Tables\Columns\TextColumn::make('referrals_count')->label('количество рефералов')
//                    ->formatStateUsing(function (callable $get) {
//                        $record = $get('id');
//                        $allLevelsReferralsCount =
//                            $record->referrals()->count() +
//                            $record->referralsSecondLevel()->count() +
//                            $record->referralsThirdLevel()->count() +
//                            $record->referralsFourthLevel()->count() +
//                            $record->referralsFifthLevel()->count();
//
//                        return $allLevelsReferralsCount;
//                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, $record) {
                        if ($record->isAdmin()) {
                            Notification::make()
                                ->warning()
                                ->title('Невозможно удалить пользователя, который является админом!')
                                ->body('Защита от случайного удаления аккаунта администратора')
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                        foreach ($records as $record) {
                            if ($record->isAdmin()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Невозможно удалить пользователя, который является админом!')
                                    ->body('Защита от случайного удаления аккаунта администратора')
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        }
                    }),

            ])
            ->headerActions([
                FilamentExportHeaderAction::make('Export'),
//                Tables\Actions\Action::make('дерево рефералов')
//                Forms\Components\Actions\Action::make('дерево рефералов')
            ])
            ->actionsPosition();
    }

    public static function getRelations(): array
    {
        return [
            ReferralsPaymentsRelationManager::class,
            ReferralsMadePaymentsRelationManager::class,
            WatchedLecturesHistoryRelationManager::class,
            DevicesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
