<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:login_codes',
        ];
    }
}
