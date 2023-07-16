<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PregnancyFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'data' => 'required|string'
        ];
    }
}
