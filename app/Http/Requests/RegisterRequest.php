<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    title: 'RegisterRequest'
)]

class RegisterRequest extends FormRequest
{
    #[OA\Property(property: 'email', description: 'email юзера', type: 'string')]
    #[OA\Property(property: 'password', description: 'пароль юзера', type: 'string')]
    #[OA\Property(property: 'password_confirmation', description: 'подтверждение пароля юзера', type: 'string')]
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:255|confirmed',
        ];
    }
}
