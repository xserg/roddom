<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Exceptions\Custom\UserCannotSaveLectureException;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Put(
    path: '/lecture/{id}/list-watch',
    description: 'Добавить лекцию в просмотренные',
    summary: 'Сохранить лекцию в список просмотренных(list watched)',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим сохранить',
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
            new OA\Property(property: 'message', type: 'string', example: 'Лекция уже в просмотренных'),
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
class AddToListWatchedLectureController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    public function __invoke(Request $request, int $lectureId)
    {
        try {
            $this->userService->addLectureToListWatched($lectureId, auth()->user());
        } catch (NotFoundHttpException $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (UserCannotSaveLectureException $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'message' => 'Лекция успешно добавлена в список просмотренных',
        ], Response::HTTP_OK);
    }
}
