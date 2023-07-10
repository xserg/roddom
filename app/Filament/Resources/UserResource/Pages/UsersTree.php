<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions\Action;
use SolutionForest\FilamentTree\Resources\Pages\TreePage as BasePage;

class UsersTree extends BasePage
{
    protected static string $resource = UserResource::class;

    protected static int $maxDepth = 2;

    protected function getActions(): array
    {
        return [

            Action::make('список_пользователей')
                ->color('secondary')
                ->url(route('filament.resources.users.index'))
//            $this->getCreateAction(),
            // SAMPLE CODE, CAN DELETE
            //\Filament\Pages\Actions\Action::make('sampleAction'),
        ];
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }
}
