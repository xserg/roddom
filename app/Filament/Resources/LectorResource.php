<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectorResource\Pages;
use App\Models\Lector;
use Closure;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;

class LectorResource extends Resource
{
    protected static ?string $model = Lector::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\DatePicker::make('career_start')
                    ->required(),
                Forms\Components\FileUpload::make('photo')
                    ->directory(function (Closure $get, string $context) {
                        if ($context == 'create') {
                            $nextId = DB::select("show table status like 'lectors'")[0]->Auto_increment;
                            return 'images/lectors' . '/' . $nextId;
                        }
                        return 'images/lectors' . '/' . $get('id');
                    })
                    ->afterStateHydrated(function (Closure $set, Forms\Components\FileUpload $component, $state) {
                        if (is_null($state)) {
                            return;
                        }

                        $redundantStr = config('app.url') . '/storage/';

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
                            $nextId = DB::select("show table status like 'lectors'")[0]->Auto_increment;
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('position'),
                Tables\Columns\ImageColumn::make('photo'),
                Tables\Columns\TextColumn::make('career_start')
                    ->date(),
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
            'index' => Pages\ListLectors::route('/'),
            'create' => Pages\CreateLector::route('/create'),
            'edit' => Pages\EditLector::route('/{record}/edit'),
        ];
    }
}
