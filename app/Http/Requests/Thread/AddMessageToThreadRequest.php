<?php

namespace App\Http\Requests\Thread;

use Illuminate\Foundation\Http\FormRequest;

class AddMessageToThreadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:65535'
        ];
    }
}
