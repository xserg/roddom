<?php

namespace App\Http\Requests\ResetPassword;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ForgotPasswordRequest',
    title: 'ForgotPasswordRequest',
    required: ['email']
)]
class ForgotPasswordRequest extends FormRequest
{
    #[OA\Property(property: 'email', description: 'email пользователя', type: 'string')]
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users',
        ];
    }
}
