<?php

namespace App\Http\Resources;

use App\Repositories\LectureRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LectureCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection
        ];
    }
}
