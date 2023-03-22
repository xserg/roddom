<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('period_id')
                    ->relationship('period', 'length')
                    ->label('период подписки, дней')
                    ->afterStateUpdated(function (\Closure $set, $context, $state, \Closure $get) {
                        if ($context == 'create') {
                            $periodLength = Period::query()->firstWhere('id', '=', $state)->length;
                            $set('end_date', Carbon::now()->timezone('Europe/Moscow')->addDays($periodLength));
                        } elseif ($context == 'edit') {
                            $periodLength = Period::query()->firstWhere('id', '=', $state)->length;
                            $set('end_date', Carbon::createFromDate($get('start_date'))->addDays($periodLength));
                        }
                    })
                    ->reactive()
                    ->required(),
                Forms\Components\Select::make('subscriptionable_type')
                    ->required()
                    ->label('подписка на')
                    ->options([
                        'App\Models\Lecture' => 'лекция',
                        'App\Models\Category' => 'категория',
                        'App\Models\Promo' => 'промо пак',
                    ])
                    ->afterStateUpdated(fn(\Closure $set) => $set('subscriptionable_id', null))
                    ->reactive(),
                Forms\Components\Select::make('subscriptionable_id')
                    ->label('id объекта подписки')
                    ->options(function (\Closure $set, \Closure $get) {
                        if ($get('subscriptionable_type') == 'App\Models\Lecture') {
                            return Lecture::all()
                                ->pluck('id_title', 'id');
                        } elseif ($get('subscriptionable_type') == 'App\Models\Category') {
                            return Category::subCategories()
                                ->pluck('title', 'id');
                        } elseif ($get('subscriptionable_type') == 'App\Models\Promo') {
                            return Promo::all()
                                ->pluck('id');
                        }
                    })
                    ->disabled(fn(\Closure $get) => is_null($get('subscriptionable_type')))
                    ->required(),
                Forms\Components\DateTimePicker::make('start_date')
                    ->afterStateHydrated(function ($state, Component $component) {
                        if (is_null($state)) {
                            $component->state(Carbon::now()->timezone('Europe/Moscow'));
                        }
                    })
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('period_id')
                    ->formatStateUsing(
                        fn(string $state): string => Period::firstWhere('id', $state)->length
                    )
                    ->label('Период покупки, дней')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptionable_type')
                    ->formatStateUsing(
                        function ($state): string {
                            if ($state == 'App\Models\Lecture') {
                                return 'лекция';
                            } elseif ($state == 'App\Models\Category') {
                                return 'категория';
                            } elseif ($state == 'App\Models\Promo') {
                                return 'промо пак лекций';
                            }
                        }
                    )
                    ->label('тип подписки')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptionable_id')
                    ->label('id объекта подписки'),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->label('начало подписки'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('конец подписки')
                    ->dateTime(),])
            ->filters([//
            ])
            ->actions([Tables\Actions\EditAction::make(),])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make(),]);
    }

    public
    static function getRelations(): array
    {
        return [
            //
        ];
    }

    public
    static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
