<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/user/profile',
    description: 'Получение данных профиля залогиненного пользователя',
    summary: 'Получение данных профиля пользователя',
    security: [['bearerAuth' => []]],
    tags: ['user'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation exception',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class ProfileRetrieveController
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = auth()->user();
        $user = $this->userService->appendLectureCountersToUser($user);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
