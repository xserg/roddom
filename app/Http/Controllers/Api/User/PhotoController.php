<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\ProfilePhotoRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[OA\Put(
    path: '/user/photo',
    description: 'Обновление фото юзера. Максимум 10 мб, форматы: jpeg, jpg, png',
    summary: 'Загрузить фото юзера',
    security: [['bearerAuth' => []]],
    tags: ['user'])
]
#[OA\RequestBody(
    description: 'Photo file',
    required: true,
    content: [new OA\MediaType(
        mediaType: 'multipart/form-data',
        schema: new OA\Schema(title: 'photo', type: 'string', format: 'binary'))],
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(
            property: 'data',
            description: 'массив с двумя url: на маленькую и большую фото пользователя. Добавить "https://api-url/storage/"
            для перед возвращенными путями к фото',
            type: 'object',
            example: [
                'data' => [
                    'images/users/2/2.jpg',
                    'images/users/2/2-small.jpg',
                ],
                'message' => 'Photo updated successfully',
            ]
        ),
        new OA\Property(
            property: 'message',
            type: 'string'
        ),
    ])
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthenticated'
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
class PhotoController
{
    public function __construct(
        private readonly UserService $service
    ) {
    }

    public function __invoke(ProfilePhotoRequest $request): JsonResponse
    {
        try {
            $paths = $this->service->saveUsersPhoto(
                auth()->user(),
                $request->validated('photo')
            );
        } catch (Throwable) {
            return response()->json([
                'data' => [],
                'message' => 'Не удалось загрузить фото',
            ]);
        }

        return response()->json([
            'data' => $paths,
            'message' => 'Фото успешно обновлено',
        ]);
    }
}
