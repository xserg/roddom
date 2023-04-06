<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RateLectureRequest',
    title: 'RateLectureRequest',
    required: ['rate']
)]
class RateLectureRequest extends FormRequest
{
    #[OA\Property(property: 'rate', description: 'рейтинг, минимум 1, максимум 10', type: 'integer', maximum: 10, minimum: 1)]
    public function rules(): array
    {
        return [
            'rate' => 'required|integer|min:1|max:10'
        ];
    }
}
