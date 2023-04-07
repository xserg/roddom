<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppInfoResource\Pages;
use App\Filament\Resources\AppInfoResource\RelationManagers;
use App\Models\AppInfo;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\TemporaryUploadedFile;

class AppInfoResource extends Resource
{
    protected static ?string $model = AppInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Динамические заголовки';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Приложение';

    protected static ?string $label = 'Динамические заголовки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('free_lecture_hours')
                        ->required()
                        ->integer()
                        ->label('часов, раз во сколько можно смотреть бесплатную лекцию'),
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
                ])->columns(4),
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('agreement_title')
                        ->required()
                        ->maxLength(255)
                        ->label('строка "прочтите соглашение"'),
                    Forms\Components\RichEditor::make('agreement_text')
                        ->maxLength(65535)
                        ->label('текст соглашения'),
                ]),

                Forms\Components\Card::make([
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
                    Forms\Components\TextInput::make('app_title')
                        ->required()
                        ->maxLength(255)
                        ->label('имя приложения'),
                ])
                    ->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\RichEditor::make('about_app')
                        ->maxLength(65535)
                        ->label('Описание приложения'),
                    Forms\Components\TextInput::make('app_author_name')
                        ->required()
                        ->maxLength(255)
                        ->label('имя автора'),
                    Forms\Components\TextInput::make('app_link_share_title')
                        ->required()
                        ->maxLength(255)
                        ->label('строка "Поделиться ссылкой"'),
                    Forms\Components\TextInput::make('app_link_share_link')
                        ->required()
                        ->maxLength(255)
                        ->label('линк приложения'),
                    Forms\Components\TextInput::make('app_show_qr_title')
                        ->required()
                        ->maxLength(255)
                        ->label('строка "Показать QR-код"'),
                    Forms\Components\FileUpload::make('app_show_qr_link')
                        ->directory('images/app')
                        ->required()
                        ->getUploadedFileNameForStorageUsing(
                            function (): string {
                                return 'qr.jpeg';
                            })
                        ->label('qr код линка приложения')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agreement_title'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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
