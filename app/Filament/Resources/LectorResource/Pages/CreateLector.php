<?php

namespace App\Filament\Resources\LectorResource\Pages;

use App\Filament\Resources\LectorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLector extends CreateRecord
{
    protected static string $resource = LectorResource::class;

    public function createAnother(): void
    {
        parent::create(false);
    }
}
