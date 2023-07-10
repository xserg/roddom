<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Widgets\TableWidget as Widget;

//use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class ReferralsOfUserWidget extends Widget
{
    public ?User $record = null;
    protected static ?string $heading = 'Рефералы 1-5 уровней';

    protected function getTableQuery(): Builder
    {
        $allLevelsReferralsId = [
            ...$this->record->referrals()->pluck('users.id')->toArray(),
            ...$this->record->referralsSecondLevel()->pluck('users.id')->toArray(),
            ...$this->record->referralsThirdLevel()->pluck('users.id')->toArray(),
            ...$this->record->referralsFourthLevel()->pluck('users.id')->toArray(),
            ...$this->record->referralsFifthLevel()->pluck('users.id')->toArray(),
        ];

        return User::query()
            ->whereIn('users.id', $allLevelsReferralsId);
    }

//    protected function getViewData(): array
//    {
//        [
//            $this->getTableQuery()->whereIn('id', $this->record->referralsSecondLevel()->pluck('id')->toArray())
//        ];
//    }

    public function getTableModelLabel(): string
    {
        return 'Рефералы';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label('имя'),
            Tables\Columns\TextColumn::make('email')->label('email'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
//            Tables\Actions\ViewAction::make()
//                ->form([
//                    Forms\Components\Select::make('company_id')
//                        ->label('Company')
//                        ->options(Company::all()->pluck('name', 'id')->toArray())
//                        ->reactive()
//                        ->afterStateUpdated(fn (callable $set) => $set('department_id', null)),
//
//                    Forms\Components\Select::make('department_id')
//                        ->label('Department')
//                        ->options(function (callable $get) {
//                            $company = Company::find($get('company_id'));
//
//                            if (! $company) {
//                                return Department::all()->pluck('name', 'id');
//                            }
//
//                            return $company->departments->pluck('name', 'id');
//                        }),
//
//                    Forms\Components\TextInput::make('code')
//                        ->required(),
//                    Forms\Components\TextInput::make('name')
//                        ->required()
//                        ->maxLength(255),
//                    Forms\Components\Select::make('type')
//                        ->required()
//                        ->options([
//                            'Current Asset' => 'Current Asset',
//                            'Fixed Asset' => 'Fixed Asset',
//                            'Tangible Asset' => 'Tangible Asset',
//                            'Intangible Asset' => 'Intangible Asset',
//                            'Operating Asset' => 'Operating Asset',
//                            'Non-Operating Asset' => 'Non-Operating Asset',
//                        ]),
//                    Forms\Components\TextInput::make('description')
//                        ->maxLength(255),
//                ]),
//
//            Tables\Actions\EditAction::make()
//                ->form([
//                    Forms\Components\Select::make('company_id')
//                        ->label('Company')
//                        ->options(Company::all()->pluck('name', 'id')->toArray())
//                        ->reactive()
//                        ->afterStateUpdated(fn (callable $set) => $set('department_id', null)),
//
//                    Forms\Components\Select::make('department_id')
//                        ->label('Department')
//                        ->options(function (callable $get) {
//                            $company = Company::find($get('company_id'));
//
//                            if (! $company) {
//                                return Department::all()->pluck('name', 'id');
//                            }
//
//                            return $company->departments->pluck('name', 'id');
//                        }),
//
//                    Forms\Components\TextInput::make('code')
//                        ->required(),
//                    Forms\Components\TextInput::make('name')
//                        ->required()
//                        ->maxLength(255),
//                    Forms\Components\Select::make('type')
//                        ->required()
//                        ->options([
//                            'Current Asset' => 'Current Asset',
//                            'Fixed Asset' => 'Fixed Asset',
//                            'Tangible Asset' => 'Tangible Asset',
//                            'Intangible Asset' => 'Intangible Asset',
//                            'Operating Asset' => 'Operating Asset',
//                            'Non-Operating Asset' => 'Non-Operating Asset',
//                        ]),
//                    Forms\Components\TextInput::make('description')
//                        ->maxLength(255),
//                ])
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableHeaderActions(): array
    {
        return [
//            Tables\Actions\CreateAction::make()
//                ->form([
//                    Forms\Components\Select::make('company_id')
//                        ->label('Company')
//                        ->options(Company::all()->pluck('name', 'id')->toArray())
//                        ->reactive()
//                        ->afterStateUpdated(fn (callable $set) => $set('department_id', null)),
//
//                    Forms\Components\Select::make('department_id')
//                        ->label('Department')
//                        ->options(function (callable $get) {
//                            $company = Company::find($get('company_id'));
//
//                            if (! $company) {
//                                return Department::all()->pluck('name', 'id');
//                            }
//
//                            return $company->departments->pluck('name', 'id');
//                        }),
//
//                    Forms\Components\TextInput::make('code')
//                        ->required(),
//                    Forms\Components\TextInput::make('name')
//                        ->required()
//                        ->maxLength(255),
//                    Forms\Components\Select::make('type')
//                        ->required()
//                        ->options([
//                            'Current Asset' => 'Current Asset',
//                            'Fixed Asset' => 'Fixed Asset',
//                            'Tangible Asset' => 'Tangible Asset',
//                            'Intangible Asset' => 'Intangible Asset',
//                            'Operating Asset' => 'Operating Asset',
//                            'Non-Operating Asset' => 'Non-Operating Asset',
//                        ]),
//                    Forms\Components\TextInput::make('description')
//                        ->maxLength(255),
//                ])
        ];
    }

}
