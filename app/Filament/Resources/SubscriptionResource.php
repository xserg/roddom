<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers\LecturesRelationManager;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\MoneyConversion;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SubscriptionResource extends Resource
{
    use MoneyConversion;

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
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Card::make([
                            Forms\Components\Select::make('user_id')
                                ->required()
                                ->multiple(function (string $context) {
                                    return $context === 'create';
                                })
                                ->options(function () {
                                    $users = User::select(['name', 'email', 'id'])->whereNot('is_admin', '1')->get();
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
                                ->visible(function (string $context) {
                                    return $context !== 'edit';
                                })
                                ->dehydrated(function (string $context) {
                                    return $context !== 'edit';
                                })
                                ->label('пользователь'),

                            Forms\Components\Placeholder::make('user_name')
                                ->content(function (Closure $get, ?Subscription $record) {
                                    $name = $record->user?->name ?? $record->user?->email;
                                    $path = UserResource::getUrl('edit', ['record' => $record->user?->id]);
                                    $classes = 'text-primary-600 transition hover:underline hover:text-primary-500 focus:underline focus:text-primary-500';

                                    return new HtmlString("<a class=$classes href=\"$path\">$name</a>");
                                })
                                ->visible(fn (string $context) => $context === 'edit')
                                ->label('Пользователь'),
                            Forms\Components\Select::make('subscriptionable_type')
                                ->required()
//                                ->disabled(fn (string $context) => $context === 'edit')
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
                                        Category::class => Category::orderBy('parent_id')->get()->mapWithKeys(fn (Category $category) => [
                                            $category->id => $category->title . ' (' . ($category->isMain()
                                                    ? 'основная) всего ' . $category->childrenCategories()->withCount('lectures')->get()->sum('lectures_count')
                                                    : 'подкатегория) всего ' . $category->lectures()->count())
                                                . ' лекций'
                                        ]),
                                        Lecture::class => Lecture::orderBy('title')->get()->mapWithKeys(fn (Lecture $lecture) => [
                                            $lecture->id => Str::limit($lecture->title, 60) . ' (' . Str::limit($lecture->category->title, 25) . ')'
                                        ]),
                                        Promo::class => Promo::all()->mapWithKeys(fn (Promo $promo) => [
                                            1 => Lecture::promo()->count() . ' лекций'
                                        ]),
                                        EverythingPack::class => [1 => Lecture::count() . ' лекций'],
                                        default => null
                                    };
                                })
                                ->optionsLimit(0)
                                ->disabled(fn (Closure $get, string $context) => is_null($get('subscriptionable_type'))
                                    || $get('subscriptionable_type') === Promo::class
                                    || $get('subscriptionable_type') === EverythingPack::class)
                                ->saveRelationshipsWhenHidden()
                                ->required(),
                        ])->columnSpan(fn(string $context) => $context === 'create' ? 3 : 2),
                        Forms\Components\Card::make([
                            Forms\Components\DateTimePicker::make('start_date')
                                ->label('начало подписки')
                                ->required(),
                            Forms\Components\DateTimePicker::make('end_date')
                                ->label('окончание подписки')
                                ->required(),
                            Forms\Components\Select::make('period_id')
                                ->relationship('period', 'length')
                                ->label('период подписки, дней(не обязательно)')
                                ->afterStateUpdated(function (Closure $set, string $context, $state, Closure $get) {
                                    if (is_null($state)) {
                                        return;
                                    }

                                    $periodLength = Period::query()->firstWhere('id', $state)->length;

                                    if ($context === 'create') {
                                        $set('start_date', now()->toDateTimeString());
                                        $set('end_date', now()->addDays($periodLength)->toDateTimeString());
                                    } elseif ($context === 'edit') {
                                        $set('end_date', Carbon::createFromDate($get('start_date'))->addDays($periodLength)->toDateTimeString());
                                    }
                                })
                                ->visible(fn (string $context) => $context === 'create')
                                ->reactive(),
                        ])->columnSpan(1),
                        Forms\Components\Card::make([
                            Forms\Components\Placeholder::make('created_at')
                                ->content(fn (?Subscription $record) => $record->created_at)
                                ->label('создана')
                                ->visible(fn ($context) => $context === 'edit')
                                ->disabled(),
                            Forms\Components\Placeholder::make('total_price')
                                ->content(fn (?Subscription $record) => self::coinsToRoubles($record->total_price) ?? 0)
                                ->label('цена')
                                ->visible(fn ($context) => $context === 'edit')
                                ->disabled(),Forms\Components\Placeholder::make('price_to_pay')
                                ->content(fn (?Subscription $record) => self::coinsToRoubles($record->price_to_pay) ?? 0)
                                ->label('оплачено рублями')
                                ->visible(fn ($context) => $context === 'edit')
                                ->disabled(),
                            Forms\Components\Placeholder::make('points')
                                ->content(fn (?Subscription $record) => self::coinsToRoubles($record->points) ?? 0)
                                ->label('оплачено бэбикоинами')
                                ->visible(fn ($context) => $context === 'edit')
                                ->disabled(),
                            Forms\Components\Placeholder::make('lectures_count')
                                ->content(fn (?Subscription $record) => $record->lectures_count)
                                ->label('куплено лекций')
                                ->visible(fn ($context) => $context === 'edit')
                                ->disabled(),

                        ])->columnSpan(1)
                            ->visible(fn (string $context) => $context === 'edit'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->sortable()
                    ->url(function (Subscription $record): string {
                        return UserResource::getUrl('edit', ['record' => $record->user_id]);
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
                    ->formatStateUsing(fn (?string $state) => self::coinsToRoubles($state ?? 0))
                    ->label('цена')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_to_pay')
                    ->formatStateUsing(fn (?string $state) => self::coinsToRoubles($state ?? 0))
                    ->label('рублями')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('points')
                    ->label('бебикоинами')
                    ->formatStateUsing(fn (?string $state) => self::coinsToRoubles($state ?? 0))
                    ->toggleable(),
                TextColumn::make('lectures_count')
                    ->label('лекций')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(16)
                    ->tooltip(fn (?Model $record): ?string => $record?->description)
                    ->label('описание'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('создана')
                    ->toggleable()
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->multiple()
                    ->options(function () {
                        $query = User::has('subscriptions')->orderBy('name')->orderBy('email');
                        $options = [];
                        $query->get()->each(function (User $user) use (&$options) {
                            return $options[$user->id] = "$user->name ($user->email)";
                        });
                        return $options;
                    })
                    ->label('По пользователю'),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('Export'),
            ])
            ->actions([Tables\Actions\EditAction::make()->after(function (RelationManager $livewire) {
                $livewire->emit('refresh');
            }), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            LecturesRelationManager::class
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
