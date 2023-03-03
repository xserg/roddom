<?php

namespace App\Http\Requests\Buy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class BuyLectureRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:lectures,id',
            'period' => 'required|exists:subscription_periods,length',
        ];
    }

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'id' => Route::input('id'),
            'period' => Route::input('period'),
        ]);
    }
}
