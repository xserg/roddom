<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    title: 'RegisterRequest',
    required: ['email', 'polis', 'password', 'password_confirmation']
)]

class RegisterRequest extends FormRequest
{
    #[OA\Property(property: 'email', description: 'email юзера. Уникальный на приложение', type: 'string')]
    #[OA\Property(property: 'polis', description: 'polis юзера. Уникальный на приложение', type: 'string')]
    #[OA\Property(property: 'password', description: 'пароль юзера', type: 'string', maxLength: 255, minLength: 6)]
    #[OA\Property(property: 'password_confirmation', description: 'подтверждение пароля юзера', type: 'string')]
    public function rules(): array
    {
        return [
            'polis' => 'required|min:16|max:16|exists:gk101.registry,polis',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:255|confirmed',
            'ref' => ''
        ];
    }
}
