<?php

namespace App\Http\Requests\ResetPassword;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdatePasswordRequest',
    title: 'UpdatePasswordRequest',
    required: ['code', 'password', 'password_confirmation']
)]
class UpdatePasswordRequest extends FormRequest
{
    #[OA\Property(property: 'code', description: 'шестизначный код из почты пользователя', type: 'string')]
    #[OA\Property(property: 'password', description: 'новый пароля', type: 'string', maxLength: 255, minLength: 6)]
    #[OA\Property(property: 'password_confirmation', description: 'подтверждение нового пароля', type: 'string')]
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:password_resets_with_code',
            'password' => 'required|string|min:6|max:255|confirmed',
        ];
    }
}
