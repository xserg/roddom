<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProfileRequest',
    title: 'ProfileRequest'
)]
class ProfileRequest extends FormRequest
{
    #[OA\Property(property: 'name', description: 'имя пользователя. max:255', type: 'string')]
    #[OA\Property(property: 'birthdate', description: 'дата рождения пользователя', type: 'string', format: 'date')]
    #[OA\Property(property: 'phone', description: 'номер телефона пользователя. min:10, max:20 символов, желательно !только цифры', type: 'string')]
    #[OA\Property(property: 'is_mother', description: 'родила ли уже пользователь', type: 'boolean', format: 'boolean')]
    #[OA\Property(property: 'pregnancy_weeks', description: 'если ещё не родила, то недели беременности. min: 1, max: 40.
    А в профиле пользователя будет возвращаться (примерная - так как только неделю знаем) дата беременности', type: 'integer')]
    #[OA\Property(property: 'baby_born', description: 'если родила, то дата рождения ребёнка', type: 'string', format: 'date')]
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:20|min:10',
            'is_mother' => 'boolean',
            'pregnancy_weeks' => 'numeric|min:1|max:40',
            'baby_born' => 'date',
        ];
    }
}
