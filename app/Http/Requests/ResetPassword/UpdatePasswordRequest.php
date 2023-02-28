<?php

namespace App\Http\Requests\ResetPassword;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:password_resets_with_code',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
