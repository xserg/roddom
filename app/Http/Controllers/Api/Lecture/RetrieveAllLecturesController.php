<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureResource;
use App\Models\Lecture;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lectures',
    description: "Получение ресурсов лекций",
    summary: "Получение ресурсов лекций",
    security: ["bearerAuth"],
    tags: ["lectures"])
]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ])
)]
#[OA\Response(response: 500, description: 'Server Error')]

class RetrieveAllLecturesController
{
    public function __invoke(): ResourceCollection
    {
        return LectureResource::collection(
            Lecture::all()
        );
    }
}
