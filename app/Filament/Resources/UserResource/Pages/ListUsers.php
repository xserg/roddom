<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected static ?string $navigationLabel = 'Пользователи';
    public array $data_list= [
        'calc_columns' => [
            'refPoints.points',
        ],
    ];

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('дерево_рефералов')
                ->color('secondary')
                ->url(route('filament.resources.users.tree-list'))
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100];
    }

    protected function getTableContentFooter(): ?View
    {
        return view('table.users-footer', $this->data_list);
    }
}
