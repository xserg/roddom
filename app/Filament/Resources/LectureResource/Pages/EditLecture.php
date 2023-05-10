<?php

namespace App\Filament\Resources\LectureResource\Pages;

use App\Filament\Resources\LectureResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLecture extends EditRecord
{
    protected static string $resource = LectureResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
