<?php

namespace App\Http\Requests\Buy;

use App\Models\Period;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class BuyPromoRequest extends FormRequest
{
    public function messages(): array
    {
        return array_merge(parent::messages(),
            [
                'period.exists' => 'Можно покупать только на периоды(в днях): ' .
                    Period::all()->pluck('length')->implode(', ')
            ]
        );
    }

    public function rules(): array
    {
        return [
            'period' => 'required|exists:subscription_periods,length',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->request->all(), [
            'period' => Route::input('period'),
        ]);
    }
}
