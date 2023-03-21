<?php

namespace App\Filament\Resources\LectureResource\Pages;

use App\Filament\Resources\LectureResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLecture extends CreateRecord
{
    protected static string $resource = LectureResource::class;

    protected static bool $canCreateAnother = false;
}
