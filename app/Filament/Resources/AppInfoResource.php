<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppInfoResource\Pages;
use App\Models\AppInfo;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Position;
use Livewire\TemporaryUploadedFile;

class AppInfoResource extends Resource
{
    protected static ?string $model = AppInfo::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'Динамические заголовки';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Приложение';
    protected static ?string $label = 'Динамические заголовки';
    protected static ?string $pluralLabel = 'Динамические заголовки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('free_lecture_hours')
                        ->required()
                        ->integer()
                        ->label('часов, раз во сколько можно смотреть бесплатную лекцию')
                        ->rules(['gte:0']),
                    Forms\Components\TextInput::make('tarif_title_1')
                        ->required()
                        ->maxLength(255)
                        ->label('тариф 1'),
                    Forms\Components\TextInput::make('tarif_title_2')
                        ->required()
                        ->maxLength(255)
                        ->label('тариф 2'),
                    Forms\Components\TextInput::make('tarif_title_3')
                        ->required()
                        ->maxLength(255)
                        ->label('тариф 3'),
                    Forms\Components\TextInput::make('credit_minimal_sum')
                        ->required()
                        ->integer()
                        ->label('минимальная сумма рассрочки/кредита')
                        ->rules(['gte:0']),
                    Forms\Components\FileUpload::make('successful_purchase_image')
                        ->directory('images/app')
                        ->required()
                        ->columnSpan(1)
                        ->getUploadedFileNameForStorageUsing(
                            function (): string {
                                return 'successful_purchase_image.jpeg';
                            })
                        ->label('изображение в майле успешной покупки'),

                    Forms\Components\RichEditor::make('successful_purchase_text')
                        ->label('текст успешной покупки, на email')
                        ->toolbarButtons([
                            'bold',
                            'h2',
                            'h3',
                            'italic',
                            'redo',
                            'strike',
                            'undo',
                            'preview',
                        ])
                        ->maxLength(65535),
                ])->columns(2),

                Forms\Components\Section::make('Раздел "Лицензионное соглашение"')
                    ->schema([
                        Forms\Components\TextInput::make('agreement_title')
                            ->required()
                            ->maxLength(255)
                            ->label('строка "прочтите соглашение"'),
                        Forms\Components\RichEditor::make('agreement_text')
                            ->toolbarButtons([
                                'bold',
                                'h2',
                                'h3',
                                'italic',
                                'redo',
                                'strike',
                                'undo',
                                'preview',
                            ])
                            ->maxLength(65535)
                            ->label('текст соглашения'),

                    ])
                    ->collapsible()
                    ->collapsed(),
                Forms\Components\Section::make('Заголовки внутри приложения')
                    ->schema([
                        Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\TextInput::make('app_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('имя приложения'),
                                Forms\Components\TextInput::make('recommended_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Рекомендуем"'),
                                Forms\Components\TextInput::make('recommended_subtitle')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Не пропустите новые лекции"'),
                                Forms\Components\TextInput::make('lectures_catalog_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Каталог лекций"'),
                                Forms\Components\TextInput::make('lectures_catalog_subtitle')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Выберите тему, которая вас интересует"'),
                                Forms\Components\TextInput::make('out_lectors_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Наши лекторы"'),
                                Forms\Components\TextInput::make('not_viewed_yet_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Вы ещё не смотрели"'),
                                Forms\Components\TextInput::make('more_in_the_collection')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Ещё в подборке"'),
                                Forms\Components\TextInput::make('about_lector_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "О лекторе"'),
                                Forms\Components\TextInput::make('diplomas_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Дипломы и сертификаты"'),
                                Forms\Components\TextInput::make('lectors_videos')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('строка "Видео от лектора"'),
                                Forms\Components\TextInput::make('validation_wrong_credentials')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Неправильный логин/пароль. Повторите попытку.'),
                                Forms\Components\TextInput::make('reset_code_sent')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Код подтверждения отправлен'),
                                Forms\Components\TextInput::make('added_to_saved')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Добавили в «Сохранённые»'),
                                Forms\Components\TextInput::make('removed_from_saved')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Удалили из «Сохранённых»'),
                                Forms\Components\TextInput::make('added_to_watched')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Добавили в «Просмотренные»'),
                                Forms\Components\TextInput::make('removed_from_watched')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Удалили из «Просмотренных»'),
                                Forms\Components\Textarea::make('message_sent')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(255)
                                    ->label('Ваше сообщение успешно отправлено.'),
                                Forms\Components\TextInput::make('message_sent_error')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Во время отправки сообщения произошла ошибка.'),
                                Forms\Components\TextInput::make('thanks_for_rate')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Спасибо за вашу оценку!'),
                                Forms\Components\Textarea::make('thanks_for_feedback')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(255)
                                    ->label('Спасибо за обратную связь! Ваше сообщение успешно отправлено.'),
                                Forms\Components\Textarea::make('buy_page_under_btn_description')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(510)
                                    ->label('Выбранный материал будет доступен Вам для просмотра в течение x дней с момента покупки.'),
                                Forms\Components\Textarea::make('buy_page_description')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(255)
                                    ->label('Вы можете приобрести доступ к этому материалу на необходимый Вам промежуток времени.'),
                                Forms\Components\TextInput::make('buy_category')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Купить категорию со скидкой'),
                                Forms\Components\TextInput::make('buy_subcategory')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Купить подкатегорию со скидкой'),
                                Forms\Components\TextInput::make('view_schedule')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('График просмотра'),
                                Forms\Components\TextInput::make('watched_already')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "Вы уже посмотрели материал на сегодня"'),
                                Forms\Components\TextInput::make('next_free_lecture_available_at')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "Следующий бесплатный будет доступен через"'),
                                Forms\Components\TextInput::make('buy_all')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "Купить весь каталог со скидкой"'),
                                Forms\Components\TextInput::make('watch_from')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "Смотреть от"'),
                                Forms\Components\TextInput::make('chosen_category_contains_lectures')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "Выбранная категория содержит х лекций."'),
                                Forms\Components\TextInput::make('your_profit_is_roubles')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "Ваша экономия составит х рублей."'),
                                Forms\Components\TextInput::make('category_special_price_text')
                                    ->label('строка «Вы также можете приобрести полностью категорию по специальной цене»')
                                    ->maxLength(255)->columnSpan(2),

                                // ref system
                                Forms\Components\TextInput::make('ref_system_title')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Партнерская программа, заголовок'),
                                Forms\Components\Textarea::make('ref_system_description')
                                    ->required()
                                    ->rows(3)
                                    ->label('Партнерская программа, описание'),
                                Forms\Components\TextInput::make('user_invites_you_to_join')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Строка "x приглашает Вас присоединиться к интересным материалам в Школе Мам и Пап"'),
                                Forms\Components\FileUpload::make('ref_system_preview_picture')
                                    ->directory('images/app')
                                    ->required()
                                    ->columnSpan(1)
                                    ->image()
                                    ->getUploadedFileNameForStorageUsing(
                                        function (TemporaryUploadedFile $file): string {
                                            return 'ref_system_preview_picture.' . $file->extension();
                                        })
                                    ->label('картинка реферальной системы'),
                            ])->columns(2),
                    ])
                    ->collapsible()
                    ->collapsed(),
                Forms\Components\Section::make('Раздел "О приложении"')
                    ->columns(2)
                    ->schema([
                        Forms\Components\RichEditor::make('about_app')
                            ->toolbarButtons([
                                'bold',
                                'h2',
                                'h3',
                                'italic',
                                'redo',
                                'strike',
                                'undo',
                                'preview',
                            ])
                            ->maxLength(65535)
                            ->columnSpan(2)
                            ->label('Описание приложения'),
                        Forms\Components\TextInput::make('app_author_name')
                            ->required()
                            ->columnSpan(1)
                            ->maxLength(255)
                            ->label('имя автора'),
                        Forms\Components\TextInput::make('app_link_share_title')
                            ->required()
                            ->columnSpan(1)
                            ->maxLength(255)
                            ->label('строка "Поделиться ссылкой"'),
                        Forms\Components\TextInput::make('app_link_share_link')
                            ->required()
                            ->columnSpan(1)
                            ->maxLength(255)
                            ->label('линк приложения'),
                        Forms\Components\TextInput::make('app_show_qr_title')
                            ->required()
                            ->columnSpan(1)
                            ->maxLength(255)
                            ->label('строка "Показать QR-код"'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(''),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->actionsPosition(Position::BeforeCells)
            ->bulkActions([
            ]);
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
            'index' => Pages\ListAppInfos::route('/'),
            'create' => Pages\CreateAppInfo::route('/create'),
            'edit' => Pages\EditAppInfo::route('/{record}/edit'),
        ];
    }
}
