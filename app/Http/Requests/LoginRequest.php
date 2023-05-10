<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    title: 'LoginRequest',
    required: ['email', 'password']
)]
class LoginRequest extends FormRequest
{
    #[OA\Property(property: 'email', description: 'email пользователя', type: 'string')]
    #[OA\Property(property: 'password', description: 'пароль пользователя', type: 'string')]
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|max:255',
        ];
    }
}
