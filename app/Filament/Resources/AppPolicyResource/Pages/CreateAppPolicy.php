<?php

namespace App\Filament\Resources\AppPolicyResource\Pages;

use App\Filament\Resources\AppPolicyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppPolicy extends CreateRecord
{
    protected static string $resource = AppPolicyResource::class;
     protected static bool $canCreateAnother = false;
}
