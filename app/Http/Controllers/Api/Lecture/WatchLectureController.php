<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureResource;
use App\Jobs\WatchLecture;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/lecture/{id}/watch',
    description: "Кидаем id лекции, получаем в ответе id видео в kinescope, теперь лекция просмотренная",
    summary: "Посмотреть лекцию",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим посмотреть',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', example: [
            'kinescope.id' => '837933399'
        ]),
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
#[OA\Response(
    response: Response::HTTP_FORBIDDEN,
    description: 'Forbidden',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string',example: 'Пользователь не может смотреть лекцию с id: 113. Пользователь не сможет посмотреть новую бесплатную лекцию ещё 24 час/часа/часов'),
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
class WatchLectureController
{
    public function __construct(
        private UserService $userService,
    )
    {
    }

    public function __invoke(Request $request, int $id): JsonResource|JsonResponse
    {
        try {
            $videoId = $this->userService->watchLecture($id, auth()->user());
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Пользователь не может смотреть лекцию с id: ' . $id . '. ' . $exception->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json(
            [
                'data' => ['kinescope.id' => $videoId]
            ],
            Response::HTTP_OK
        );
    }
}
