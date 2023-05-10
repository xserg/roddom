<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfilePhotoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'photo' => 'image|mimes:jpeg,png,jpg|max:10240',
        ];
    }
}
