<?php

namespace App\Http\Requests\ResetPassword;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CodeRequest',
    title: 'CodeRequest',
    required: ['code']
)]
class CodeRequest extends FormRequest
{
    #[OA\Property(property: 'code', description: 'код, который прислан пользователю в email', type: 'string')]
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:password_resets_with_code',
        ];
    }
}
