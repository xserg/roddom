<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->maxLength(255)
                        ->label('Имя'),
                    Forms\Components\FileUpload::make('photo')
                        ->directory('images/users')
                        ->label('Фото пользователя')
//                        if ($context == 'create') {
//                            $nextId = DB::select("show table status like 'users'")[0]->Auto_increment;
//                            return 'images/users' . '/' . $nextId;
//                        }
//                        return 'images/users' . '/' . $get('id');
//                    })
//                    ->afterStateHydrated(function (Closure $set, Forms\Components\FileUpload $component, $state) {
//                        $redundantStr = config('app.url') . '/storage/';
//
//                        if (is_null($state)) {
//                            return;
//                        }
//
//                        if (Str::contains($state, $redundantStr)) {
//                            $component->state([Str::remove($redundantStr, $state)]);
//                        } else {
//                            $component->state([$state]);
//                        }
//                    })
//                    ->dehydrateStateUsing(
//                        function (Closure $set, $state, Closure $get) {
//                            return config('app.url') . '/storage/' . Arr::first($state);
//                        })
//                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Closure $get, string $context): string {
//                        if ($context == 'create') {
//                            $nextId = DB::select("show table status like 'users'")[0]->Auto_increment;
//                            return (string)$nextId . '.' . $file->getClientOriginalExtension();
//                        }
//                        return (string)$get('id') . '.' . $file->getClientOriginalExtension();
//                    })
                        ->maxSize(10240)
                        ->image()
                        ->imageResizeMode('force')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('300')
                        ->imageResizeTargetHeight('300'),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
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
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->visible(false),
                    Forms\Components\DateTimePicker::make('next_free_lecture_available')
                        ->label('Дата, когда можно смотреть бесплатную лекцию'),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->visible(fn (Component $livewire): bool => $livewire instanceof Pages\CreateUser),
                ])->columns(2),

                /*
                 * SUBSCRIPTIONS - REPEATER
                 */
                Forms\Components\Card::make([
                    Repeater::make('subscriptions')
                        ->relationship('subscriptions')
                        ->label('Подписки')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\Select::make('period_id')
                                ->relationship('period', 'length')
                                ->label('период подписки, дней')
                                ->afterStateUpdated(function (Closure $set, string $context, $state, Closure $get) {
                                    $periodLength = Period::query()->firstWhere('id', $state)->length;

                                    if ($context === 'create') {
                                        $set('start_date', now());
                                        $set('end_date', now()->addDays($periodLength));
                                    } elseif ($context === 'edit') {
                                        $set('start_date', now());
                                        $set('end_date', Carbon::createFromDate($get('start_date'))->addDays($periodLength));
                                    }
                                })
                                ->reactive()
                                ->required(),
                            Forms\Components\Select::make('subscriptionable_type')
                                ->required()
                                ->label('тип подписки')
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
                                ->label('объект подписки')
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
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\DateTimePicker::make('start_date')
                                ->label('начало подписки')
                                ->required(),
                            Forms\Components\DateTimePicker::make('end_date')
                                ->label('окончание подписки')
                                ->required(),
                        ])
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('имя')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->label('email')->sortable()->searchable(),
                Tables\Columns\ImageColumn::make('photo')->label('фото')->toggleable(),
                Tables\Columns\TextColumn::make('birthdate')
                    ->date()
                    ->label('дата рождения')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')->label('телефон')->toggleable(),
                Tables\Columns\IconColumn::make('is_mother')->label('родился ли ребёнок')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('pregnancy_start')->label('дата начала беременности')->sortable()->date()->toggleable(),
                Tables\Columns\TextColumn::make('profile_fulfilled_at')->label('дата заполнения профиля')->sortable()->toggleable(),
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
            //
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
