<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

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
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('birthdate'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\Toggle::make('is_mother')
                    ->required(),
                Forms\Components\DatePicker::make('pregnancy_start'),
                Forms\Components\DatePicker::make('baby_born'),
                Forms\Components\FileUpload::make('photo')
                    ->directory('images/users')
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

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->visible(false),
                Forms\Components\DateTimePicker::make('next_free_lecture_available'),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->visible(fn (Component $livewire): bool => $livewire instanceof Pages\CreateUser),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\ImageColumn::make('photo'),
                Tables\Columns\TextColumn::make('birthdate')
                    ->date(),
                Tables\Columns\TextColumn::make('phone'),
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
