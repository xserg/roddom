<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/user/profile',
    description: "Получения данных профиля пользователя",
    summary: "Получения данных профиля пользователя",
    security: [["bearerAuth" => []]],
    tags: ["user"])
]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
    ])
)]
#[OA\Response(
    response: 422,
    description: 'Validation exception',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(response: 500, description: 'Server Error')]
class ProfileRetrieveController
{
    public function __construct(
//        private UserService $service
    )
    {
    }

    public function __invoke(
    ): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = auth()->user();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
