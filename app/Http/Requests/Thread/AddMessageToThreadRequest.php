<?php

namespace App\Http\Requests\Thread;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AddMessageToThreadRequest',
    title: 'AddMessageToThreadRequest',
    required: ['message']
)]
class AddMessageToThreadRequest extends FormRequest
{
    #[OA\Property(property: 'message', description: 'текст сообщения, строка, не более 65535 символов', type: 'string')]
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:65535'
        ];
    }
}
