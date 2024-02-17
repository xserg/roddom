<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\RefPoints;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected static ?string $navigationLabel = 'Пользователи';
    public array $data_list = [
        'calc_columns' => [
            'refPoints.points',
        ],
        'common_babycoins' => 0
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->data_list['common_babycoins'] = RefPoints::query()
            ->whereHas('user', fn ($query) => $query->where('is_admin', false))
            ->sum('points');
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
