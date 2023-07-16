<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WizardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'index' => $this->order,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'form' => $this->form,
        ];
    }
}
