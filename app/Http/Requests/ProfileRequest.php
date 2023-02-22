<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProfileRequest',
    title: 'ProfileRequest'
)]
class ProfileRequest extends FormRequest
{
    #[OA\Property(property: 'name', description: 'имя пользователя', type: 'string')]
    #[OA\Property(property: 'birthdate', description: 'дата рождения пользователя', type: 'string', format: 'date')]
    #[OA\Property(property: 'phone', description: 'номер телефона пользователя', type: 'string')]
    #[OA\Property(property: 'is_mother', description: 'родила ли уже пользователь', type: 'string', format: 'boolean')]
    #[OA\Property(property: 'pregnancy_weeks', description: 'если ещё не родила, то недели беременности', type: 'string')]
    #[OA\Property(property: 'baby_born', description: 'если родила, то дата рождения ребёнка', type: 'string', format: 'date')]
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'birthdate' => 'date',
            'phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|max:20|min:10',
            'is_mother' => 'boolean',
            'pregnancy_weeks' => 'numeric|min:0|max:76',
            'baby_born' => 'date'
        ];
    }
}
