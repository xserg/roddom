<?php

namespace App\Http\Requests\Buy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class BuyPromoRequest extends FormRequest
{
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
