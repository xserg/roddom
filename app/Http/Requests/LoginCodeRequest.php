<?php

namespace App\Http\Requests;

use App\Dto\User\LoginCodeDto;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginCodeRequest',
    title: 'LoginCodeRequest',
    required: ['user_id', 'lector_id', 'content']
)]
class LoginCodeRequest extends FormRequest
{
    #[OA\Property(property: 'code', description: 'email пользователя', type: 'string')]
    #[OA\Property(property: 'device_name', description: 'пароль пользователя', type: 'string')]
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:login_codes',
            'device_name' => 'sometimes|string'
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning(sprintf('фэйлед валидэйшен: %s', $this->get('code')));

        parent::failedValidation($validator);
    }

    public function getDto(): LoginCodeDto
    {
        return new LoginCodeDto($this->get('code'), $this->get('device_name'));
    }
}
