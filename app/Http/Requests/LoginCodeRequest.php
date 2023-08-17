<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LoginCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:login_codes',
            'device_name' => 'string'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::error("фэйлед валидэйшен: $this->code");
        parent::failedValidation($validator); // TODO: Change the autogenerated stub
    }
}
