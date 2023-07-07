<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\ReferralsMadePaymentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReferralsOfReferralsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReferralsPaymentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReferralsRelationManager;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\RefPoints;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            ->withCount(['referrals', 'referralsOfReferrals']);
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
                        ->label('Бебикоины')
                ])
                    ->relationship('refPoints')
                    ->columnSpan(1),


                Forms\Components\Card::make([
//                    Forms\Components\TextInput::make('ref_token')
//                        ->unique('users', 'ref_token', ignoreRecord: true)
//                        ->formatStateUsing(function (string $context, Closure $set) use($uuid) {
//                            if ($context === 'create') {
//                                return $uuid;
//                            }
//                        })
//                        ->afterStateUpdated(function(Closure $set, string $state){
//                            $set('r1', route('v1.register', ['ref' => $state]));
//                        })
//                        ->maxLength(255)
//                        ->label('токен для реферальной ссылки')
//                        ->reactive()
//                        ->required(),
                    Forms\Components\Select::make('referrer_id')
                        ->options(function () {
                            $users = User::select(['name', 'email', 'id'])->get();
                            $options = [];
                            foreach ($users as $user) {
                                $options[$user->id] = $user->name ?? $user->email;
                            }
                            return $options;
                        })
                        ->label('Реферер'),
//                    Forms\Components\TextInput::make('ref-link')
//                        ->formatStateUsing(function (Closure $get, string $context, ?User $record) use ($uuid) {
//                            if ($context === 'create') {
//                                return route('v1.register', ['ref' => $uuid->toString()]);
//                            }
//                            return route('v1.register', ['ref' => $record->ref_token]);
//                        })
//                        ->label('Реферальная ссылка')
//                        ->disabled()
//                        ->reactive()
//                        ->required()
//                        ->columnSpan(2),
                ])->columns(1)->columnSpan(1),

                Forms\Components\Placeholder::make('referrals_count')
                    ->label('Количество рефералов')
                    ->content(fn (?Model $record) => $record->referrals_count)
                    ->columnSpan(2),

                Forms\Components\Placeholder::make('referrals_of_referrals_count')
                    ->label('Количество рефералов рефералов')
                    ->content(fn (?Model $record) => $record->referrals_of_referrals_count),
//                Forms\Components\TextInput::make('referrals_count')
//                    ->label('Количество рефералов'),
                /*
                 * SUBSCRIPTIONS - REPEATER
                 */
                Forms\Components\Grid::make()
                    ->schema([
                        Repeater::make('subscriptions')
                            ->relationship('subscriptions')
                            ->label('Подписки')
                            ->columnSpan(1)
                            ->columns(2)
                            ->createItemButtonLabel('Добавить подписку')
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
                                    ->reactive(),
                                Forms\Components\Select::make('period_id')
                                    ->relationship('period', 'length')
                                    ->getOptionLabelFromRecordUsing(fn (?Model $record) => "дней: {$record->length}")
                                    ->placeholder('период, дней')
                                    ->disableLabel()
                                    ->afterStateUpdated(function (Closure $set, string $context, $state, Closure $get) {
                                        if (is_null($state)) {
                                            return;
                                        }

                                        $periodLength = Period::query()->firstWhere('id', $state)->length;
                                        $set('start_date', now()->toDateTimeString());
                                        $set('end_date', now()->addDays($periodLength)->toDateTimeString());
                                    })
                                    ->reactive()
                                    ->required(),
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
                                    ->disabled(fn (Closure $get) => is_null($get('subscriptionable_type')) ||
                                        $get('subscriptionable_type') === Promo::class ||
                                        $get('subscriptionable_type') === EverythingPack::class)
//                                    ->visible(fn (Closure $get) =>
//                                        $get('subscriptionable_type') === Lecture::class ||
//                                        $get('subscriptionable_type') === Category::class
//                                    )
                                    ->dehydrated()
                                    ->required()
                                    ->disableLabel()
                                    ->columnSpan(2),
                                Forms\Components\DateTimePicker::make('start_date')
                                    ->placeholder('начало подписки')
                                    ->disableLabel()
                                    ->required(),
                                Forms\Components\DateTimePicker::make('end_date')
                                    ->placeholder('окончание подписки')
                                    ->disableLabel()
                                    ->required(),
                            ])
                            ->visible(fn (string $context) => $context === 'edit')
                    ])
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
                Tables\Columns\TextColumn::make('refPoints.points')->label('бебикоины')->toggleable()
                    ->formatStateUsing(fn (?string $state) => number_format($state / 100, 2, thousands_separator: ''))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_mother')->label('родился ли ребёнок')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('pregnancy_start')->label('дата начала беременности')->sortable()->date()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('дата создания')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('profile_fulfilled_at')->label('дата заполнения профиля')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('referrals_count')->label('количество рефералов')->counts('referrals')->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->actionsPosition();
    }

    public static function getRelations(): array
    {
        return [
            ReferralsPaymentsRelationManager::class,
            ReferralsMadePaymentsRelationManager::class,
            ReferralsRelationManager::class,
            ReferralsOfReferralsRelationManager::class,
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
