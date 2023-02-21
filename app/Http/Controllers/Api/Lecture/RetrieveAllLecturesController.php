<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureCollection;
use App\Models\Lecture;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lectures',
    description: "Получение ресурсов лекций",
    summary: "Получение ресурсов лекций",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ],
        example: [
            "data" => [
                [
                    "id" => 0,
                    "lector_id" => 0,
                    "category_id" => 0,
                    "title" => "string",
                    "preview_picture" => "string",
                    "video_id" => 0,
                ],
            ]])
)]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveAllLecturesController
{
    public function __invoke(): ResourceCollection
    {
        return LectureCollection::make(
            Lecture::all()
        );
    }
}
