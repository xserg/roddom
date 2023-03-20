<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FeedbackRequest',
    title: 'FeedbackRequest',
    required: ['user_id', 'lector_id', 'content']
)]
class FeedbackRequest extends FormRequest
{
    #[OA\Property(property: 'user_id', description: 'id пользователя', type: 'integer')]
    #[OA\Property(property: 'lector_id', description: 'id лектора', type: 'integer')]
    #[OA\Property(property: 'content', description: 'текст отзыва', type: 'string')]
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'lector_id' => 'required|exists:lectors,id',
            'content' => 'required',
        ];
    }
}
