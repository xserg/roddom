<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ProfilePhotoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'photo' => 'image|max:10240'
        ];
    }
}
