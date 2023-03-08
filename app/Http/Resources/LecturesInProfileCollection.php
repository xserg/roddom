<?php

namespace App\Http\Resources;

use App\Repositories\LectureRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LecturesInProfileCollection extends ResourceCollection
{
    public $collects = LecturesInProfileResource::class;

    public function toArray(Request $request): array
    {
        return  $this->collection->toArray();
    }
}
