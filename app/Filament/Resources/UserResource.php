<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

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
                    ->directory(function (Closure $get, string $context) {
                        if ($context == 'create') {
                            $nextId = DB::select("show table status like 'users'")[0]->Auto_increment;
                            return 'images/users' . '/' . $nextId;
                        }
                        return 'images/users' . '/' . $get('id');
                    })
                    ->afterStateHydrated(function (Closure $set, Forms\Components\FileUpload $component, $state) {
                        $redundantStr = config('app.url') . '/storage/';

                        if (is_null($state)) {
                            return;
                        }

                        if (Str::contains($state, $redundantStr)) {
                            $component->state([Str::remove($redundantStr, $state)]);
                        } else {
                            $component->state([$state]);
                        }
                    })
                    ->dehydrateStateUsing(
                        function (Closure $set, $state, Closure $get) {
                            return config('app.url') . '/storage/' . Arr::first($state);
                        })
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Closure $get, string $context): string {
                        if ($context == 'create') {
                            $nextId = DB::select("show table status like 'users'")[0]->Auto_increment;
                            return (string)$nextId . '.' . $file->getClientOriginalExtension();
                        }
                        return (string)$get('id') . '.' . $file->getClientOriginalExtension();
                    })
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
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->visible(fn(Component $livewire): bool => $livewire instanceof Pages\CreateUser),
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
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
