<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\LectureViews;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $navigationLabel = 'Пользователи';

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(function (?Model $record) {
                    if ($record && $record->isAdmin()) {
                        return false;
                    } else {
                        return true;
                    }
                }),
//            Actions\Action::make('refs')
//                ->url(fn (?User $record): string => UserResource::getUrl('refs', ['record' => $record]))
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }

    protected function getFooterWidgets(): array
    {
        return [
            UserResource\Widgets\ReferralsOfUserWidget::class,
            LectureViews::class
        ];
    }
}
