<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefreshRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'refresh_token' => 'required|exists:refresh_tokens,token'
        ];
    }
}
