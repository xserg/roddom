<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureResource;
use App\Models\Lecture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lecture/{id}',
    description: "Получение ресурса лекции",
    summary: "Получение ресурса лекции",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим получить',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ])
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ])
)]

class RetrieveLectureController
{
    public function __invoke(Request $request, int $id): JsonResource|JsonResponse
    {
        $lecture = Lecture::query()
            ->with('lector')
            ->where(['id' => $id])
            ->first();

        if(! $lecture){
            return response()->json([
                'message' => 'Lecture with id ' . $id . ' was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return LectureResource::make($lecture);
    }
}
