<?php

namespace App\Http\Requests\Buy;

use App\Models\Period;
use App\Rules\UserPointsLte;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class BuyCategoryRequest extends FormRequest
{
    public function messages(): array
    {
        return array_merge(parent::messages(),
            [
                'id.exists' => 'Не существует категории с таким id',
                'period.exists' => 'Можно покупать только на периоды(в днях): ' .
                    Period::all()->pluck('length')->implode(', '),
            ]
        );
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:lecture_categories,id',
            'period' => 'required|exists:subscription_periods,length',
            'ref_points' => [
                'required',
                'numeric',
                'min:0',
                new UserPointsLte
            ]
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'id' => Route::input('id'),
            'period' => Route::input('period'),
        ]);
    }
}
