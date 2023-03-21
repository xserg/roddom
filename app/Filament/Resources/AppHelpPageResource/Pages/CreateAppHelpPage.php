<?php

namespace App\Filament\Resources\AppHelpPageResource\Pages;

use App\Filament\Resources\AppHelpPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppHelpPage extends CreateRecord
{
    protected static string $resource = AppHelpPageResource::class;
}