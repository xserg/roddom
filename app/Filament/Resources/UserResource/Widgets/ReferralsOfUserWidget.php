<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as Widget;

//use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReferralsOfUserWidget extends Widget
{
    public ?User $record = null;
    protected static ?string $heading = 'Рефералы 1-5 уровней';

    protected function getTableQuery(): Builder
    {
        $allLevelsReferralsId = [
            ...$this->record->referrals->pluck('id')->toArray(),
            ...$this->record->referralsSecondLevel->pluck('id')->toArray(),
            ...$this->record->referralsThirdLevel->pluck('id')->toArray(),
            ...$this->record->referralsFourthLevel->pluck('id')->toArray(),
            ...$this->record->referralsFifthLevel->pluck('id')->toArray(),
        ];

        $query = User::query()
            ->whereIn('users.id', $allLevelsReferralsId);

        if (count($allLevelsReferralsId) > 0) {
            $ids = implode(',', $allLevelsReferralsId);
            $query->orderByRaw("FIELD(id, $ids)");
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        $allLevelsReferralsId = [
            '1' => $this->record->referrals->pluck('id')->toArray(),
            '2' => $this->record->referralsSecondLevel->pluck('id')->toArray(),
            '3' => $this->record->referralsThirdLevel->pluck('id')->toArray(),
            '4' => $this->record->referralsFourthLevel->pluck('id')->toArray(),
            '5' => $this->record->referralsFifthLevel->pluck('id')->toArray(),
        ];

        return [
            Tables\Columns\TextColumn::make('name')
                ->label('пользователь')
                ->formatStateUsing(fn (?User $record) => $record->name ?? $record->email)
                ->url(fn (?User $record) => UserResource::getUrl('edit', ['record' => $record->id])),
            Tables\Columns\TextColumn::make('depth')->label('уровень')
                ->formatStateUsing(function (?Model $record) use ($allLevelsReferralsId) {
                    foreach ($allLevelsReferralsId as $depth => $ids) {
                        if (in_array($record->id, $ids)) {
                            return $depth;
                        }
                    }
                    return 'не определен';
                }),
        ];
    }
}
