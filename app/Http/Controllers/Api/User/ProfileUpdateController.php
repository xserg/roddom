<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/user/profile',
    description: 'Обновление профиля пользователя',
    summary: 'Обновление профиля пользователя',
    security: [['bearerAuth' => []]],
    tags: ['user'])
]
#[OA\RequestBody(
    description: 'Данные профиля',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ProfileRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/ProfileRequest')),
    ]
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
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
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error'
)]
class ProfileUpdateController
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(
        ProfileRequest $request
    ): JsonResponse {
        $user = auth()->user();

        $user = $this->userService->saveProfile($user, $request->validated());

        $user = $this->userService->appendLectureCountersToUser($user);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
