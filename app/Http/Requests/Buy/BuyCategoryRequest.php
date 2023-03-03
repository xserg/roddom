<?php

namespace App\Http\Requests\Buy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class BuyCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                Rule::exists('lecture_categories')
                    ->where(function (Builder $query) {
                        return $query->where('parent_id', '!=', 0);
                    }),
            ],
            'period' => 'required|exists:subscription_periods,length',
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
