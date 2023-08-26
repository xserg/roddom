<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Services\UserService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Delete(
    path: '/lecture/{id}/list-watch',
    description: 'Удалить лекцию из списка просмотренных',
    summary: 'Удалить лекцию из списка просмотренных',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим удалить из из списка просмотренных',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthenticated')]
#[OA\Response(
    response: Response::HTTP_FORBIDDEN,
    description: 'Forbidden',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Лекция уже удалена из сохраненных'
            ),
        ])
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Лекция с таким id не найдена'),
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
class RemoveFromListWatchedLectureController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    public function __invoke(Request $request, int $lectureId)
    {
        $this->userService->removeLectureFromListWatched($lectureId, auth()->user());

        return response()->json([
            'message' => 'Лекция успешно удалена из списка просмотренных',
        ], Response::HTTP_OK);
    }
}
