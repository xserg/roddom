<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureResource;
use App\Repositories\LectureRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/lecture/{id}',
    description: 'Получение ресурса лекции',
    summary: 'Получение ресурса лекции',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим получить',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthenticated'
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
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
class RetrieveLectureController
{
    public function __construct(
        private LectureRepository $lectureRepository,
    ) {
    }

    public function __invoke(Request $request, int $id)
    {
        $builder = $this->lectureRepository->getLectureByIdQuery($id,
            ['lector', 'lector.diplomas', 'contentType', 'paymentType']);
        $lecture = $builder->firstOrFail()->append(['prices', 'purchase_info']);

        LectureResource::withoutWrapping();
        return LectureResource::make($lecture);
    }
}
