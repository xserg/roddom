<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\LectureContentType;
use App\Models\LecturePaymentType;
use App\Models\Promo;
use App\Repositories\PromoRepository;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Лекции';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Лекции';
    protected static ?string $pluralModelLabel = 'Лекции';
    protected static ?string $modelLabel = 'Лекция';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('id')
                        ->label('ID, заполняется автоматически')
                        ->disabled()
                        ->visible(false),
                    Forms\Components\TextInput::make('title')
                        ->label('Наименование лекции')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('lector_id')
                        ->label('Лектор')
                        ->relationship('lector', 'name')
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->label('Подкатегория лекции')
                        ->options(Category::subcategories()->get()->pluck('title', 'id'))
                        ->required(),
                    Forms\Components\FileUpload::make('preview_picture')
                        ->directory('images/lectures')
                        ->label('Превью лекции')
                        ->maxSize(10240)
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('4:3')
                        ->imageResizeTargetWidth('640')
                        ->imageResizeTargetHeight('480'),
                ])->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\RichEditor::make('description')
                        ->label('Описание лекции')
                        ->toolbarButtons([
                            'bold',
                            'h2',
                            'h3',
                            'italic',
                            'redo',
                            'strike',
                            'undo',
                            'preview'
                        ])
                        ->maxLength(65535),
                ]),
                Forms\Components\Section::make('Тип контента лекции')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('content_type_id')
                            ->options(LectureContentType::all()->pluck('title_ru', 'id')->toArray())
                            ->reactive()
                            ->label('Тип')
                            ->required()
                            ->afterStateUpdated(function (callable $set, callable $get, ?Model $record, string $context) {
                                if ($context === 'create') {
                                    return;
                                }

                                if ($record->contentType->id != $get('content_type_id')) {
                                    $set('content', null);
                                } elseif (
                                    $record->contentType->id == $get('content_type_id')
                                    && $record->contentType->id == LectureContentType::PDF
                                ) {
                                    $set('content', [$record->content]);
                                } elseif (
                                    $record->contentType->id == $get('content_type_id')
                                ) {
                                    $set('content', $record->content);
                                }
                            }),

                        Forms\Components\TextInput::make('content')
                            ->label('kinescope id')
                            ->visible(function (callable $get) {
                                return $get('content_type_id') == LectureContentType::KINESCOPE;
                            })
                            ->required()
                            ->afterStateHydrated(function (TextInput $component, ?Model $record, string $context) {
                                if ($context === 'create') {
                                    return;
                                }

                                if ($record->contentType->id != LectureContentType::PDF) {
                                    $component->state($record->content);
                                } else {
                                    $component->state([$record->content]);
                                }
                            }),

                        Forms\Components\FileUpload::make('content')
                            ->label('pdf')
                            ->directory('pdf')
                            ->required()
                            ->visible(function (callable $get) {
                                return $get('content_type_id') == LectureContentType::PDF;
                            })
                            ->afterStateHydrated(function (Forms\Components\FileUpload $component, ?Model $record, string $context) {
                                if ($context === 'create') {
                                    return;
                                }

                                if ($record->contentType->id != LectureContentType::PDF) {
                                    $component->state($record->content);
                                } else {
                                    $component->state([$record->content]);
                                }
                            }),

                        Forms\Components\TextInput::make('content')
                            ->label('ссылка на youtube/rutube видео')
                            ->visible(function (callable $get) {
                                return $get('content_type_id') == LectureContentType::EMBED;
                            })
                            ->required()
                            ->afterStateHydrated(function (TextInput $component, ?Model $record, string $context) {
                                if ($context === 'create') {
                                    return;
                                }

                                if ($record->contentType->id != LectureContentType::PDF) {
                                    $component->state($record->content);
                                } else {
                                    $component->state([$record->content]);
                                }
                            }),
                    ]),
                Forms\Components\Section::make('Форма распространения')
                    ->schema([

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('payment_type_id')
                                ->options(LecturePaymentType::all()->pluck('title_ru', 'id'))
                                ->label('Форма распространения')
                                ->required()->columnSpan(1),
                        ]),
                        Forms\Components\Grid::make(1)->schema([
                            Forms\Components\Toggle::make('show_tariff_1')
                                ->label('тариф 1'),
                            Forms\Components\Toggle::make('show_tariff_2')
                                ->label('тариф 2'),
                            Forms\Components\Toggle::make('show_tariff_3')
                                ->label('тариф 3'),
                        ])->columnSpan(1),
                        Forms\Components\Grid::make(1)->schema([
                            Forms\Components\Toggle::make('is_published')
                                ->required()
                                ->label('опубликованная'),
                            Forms\Components\Toggle::make('is_recommended')
                                ->label('рекомендованная')
                                ->required(),
                        ])->columnSpan(1),
                    ])->columns(2),


                Forms\Components\Grid::make(3)
                    ->visible(function (string $context) {
                        if ($context == 'create') return false;
                        return true;
                    })
                    ->schema([
                        Forms\Components\Fieldset::make('общие цены, категория')
                            ->label(function (?Model $record) {
                                return new HtmlString(
                                    'общая цена лекции, указывается в <a style="color: #0000EE" href="'
                                    . route('filament.resources.categories.edit', ['record' => $record->category->id])
                                    . '" target="_blank">категории</a>. Эти карточки для информации. Для того чтобы не переходить на
 страницу категории/промо пака и смотреть общие цены'
                                );
                            })
                            ->schema([
                                TextInput::make('custom_price-1')
                                    ->formatStateUsing(function (?Model $record) {
                                        return $record?->category->prices[0]['price_for_one_lecture'];
                                    })
                                    ->label(function (?Model $record) {
                                        return "период, дней: " . $record?->category->prices[0]['length'];
                                    })
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->visible(fn(string $context) => $context != 'create'),
                                TextInput::make('custom_price-2')
                                    ->formatStateUsing(function (?Model $record) {
                                        return $record?->category->prices[1]['price_for_one_lecture'];
                                    })
                                    ->label(function (?Model $record) {
                                        return "период, дней: " . $record?->category->prices[1]['length'];
                                    })
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->visible(fn(string $context) => $context != 'create'),
                                TextInput::make('custom_price-3')
                                    ->formatStateUsing(function (?Model $record) {
                                        return $record?->category->prices[2]['price_for_one_lecture'];
                                    })
                                    ->label(function (?Model $record) {
                                        return "период, дней: " . $record?->category->prices[2]['length'];
                                    })
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->visible(fn(string $context) => $context != 'create')

                            ])
                            ->columnSpan(1),
                        Forms\Components\Fieldset::make('общие цены, промо')
                            ->label(function (?Model $record) {
                                return new HtmlString(
                                    'общие цены промо лекции, указывается в <a style="color: #0000EE" href="'
                                    . route('filament.resources.promos.edit', ['record' => 1, 'activeRelationManager' => 0])
                                    . '" target="_blank">акционном паке</a>. Эти карточки для информации. Для того чтобы не переходить на
 страницу категории/промо пака и смотреть общие цены'
                                );
                            })
                            ->schema([
                                TextInput::make('custom_promo-price-1')
                                    ->formatStateUsing(function () {
                                        $promo = Promo::first();
                                        if (!$promo) return false;
                                        $prices = app(PromoRepository::class)->getPrices($promo);

                                        return $prices[0]['price_for_one_lecture'];
                                    })
                                    ->label(function (?Model $record) {
                                        return "период, дней: " . $record?->category->prices[0]['length'];
                                    })
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->visible(fn(string $context) => $context != 'create'),
                                TextInput::make('custom_promo_price-2')
                                    ->formatStateUsing(function () {
                                        $promo = Promo::first();
                                        if (!$promo) return false;
                                        $prices = app(PromoRepository::class)->getPrices($promo);

                                        return $prices[1]['price_for_one_lecture'];
                                    })
                                    ->label(function (?Model $record) {
                                        return "период, дней: " . $record?->category->prices[1]['length'];
                                    })
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->visible(fn(string $context) => $context != 'create'),
                                TextInput::make('custom_promo_price-3')
                                    ->formatStateUsing(function () {
                                        $promo = Promo::first();
                                        if (!$promo) return false;
                                        $prices = app(PromoRepository::class)->getPrices($promo);

                                        return $prices[2]['price_for_one_lecture'];
                                    })
                                    ->label(function (?Model $record) {
                                        return "период, дней: " . $record?->category->prices[2]['length'];
                                    })
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->visible(fn(string $context) => $context != 'create')
                            ])
                            ->columnSpan(1)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Наименование')
                    ->limit(25)
                    ->tooltip(fn(Model $record): string => $record->title)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Подкатегория')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => isset($record->category) ? $record->category->title : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lector.name')
                    ->label('Лектор')
                    ->limit(15)
                    ->tooltip(fn(Model $record): string => isset($record->lector) ? $record->lector->name : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contentType.title_ru')
                    ->label('Тип')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rate_avg')
                    ->getStateUsing(
                        function (?Lecture $record): ?string {
                            return round($record?->rates['rate_avg'], 1) ?: 'нет оценок';
                        }
                    )
                    ->label('Рейтинг, из 10'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубликована')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('is_free')
                    ->query(fn(Builder $query): Builder => $query->where('payment_type_id', '=', LecturePaymentType::FREE))
                    ->label('бесплатные'),
                Filter::make('payed')
                    ->query(fn(Builder $query): Builder => $query->where('payment_type_id', '=', LecturePaymentType::PAY))
                    ->label('платные'),
                Filter::make('promo')
                    ->query(fn(Builder $query): Builder => $query->where('payment_type_id', '=', LecturePaymentType::PROMO))
                    ->label('промо'),
                Filter::make('is_recommended')
                    ->query(fn(Builder $query): Builder => $query->where('is_recommended', true))
                    ->label('рекомендованные'),
                Filter::make('kinescope')
                    ->query(fn(Builder $query): Builder => $query->where('content_type_id', LectureContentType::KINESCOPE))
                    ->label('kinescope'),
                Filter::make('pdf')
                    ->query(fn(Builder $query): Builder => $query->where('content_type_id', LectureContentType::PDF))
                    ->label('pdf'),
                Filter::make('embed')
                    ->query(fn(Builder $query): Builder => $query->where('content_type_id', LectureContentType::EMBED))
                    ->label('youtube/rutube'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\DissociateBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PricesForLecturesRelationManager::class,
            RelationManagers\PromoLecturesPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLectures::route('/'),
            'create' => Pages\CreateLecture::route('/create'),
            'edit' => Pages\EditLecture::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
