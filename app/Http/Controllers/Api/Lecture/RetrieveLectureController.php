<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureResource;
use App\Jobs\WatchLecture;
use App\Repositories\LectureRepository;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
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
    public function __construct(
        private LectureRepository $repository,
        private UserService $service
    )
    {
    }

    public function __invoke(Request $request, int $id): JsonResource|JsonResponse
    {
        $lecture = $this->repository->getLectureById($id);
        $currentUser = auth()->user();

        if(! $lecture){
            return response()->json([
                'message' => 'Lecture with id ' . $id . ' was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $userCanWatch = $this->service->canUserWatchLecture($id, $currentUser);

        if(! $userCanWatch){
            return response()->json([
                'message' => 'User cannot watch lecture with id ' . $id
            ], Response::HTTP_FORBIDDEN);
        }

        WatchLecture::dispatch($lecture->id, $currentUser);

        return response()->json(
            new LectureResource($lecture),
            Response::HTTP_OK
        );
    }
}
