<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/user/profile',
    description: "Получение данных профиля залогиненного пользователя",
    summary: "Получение данных профиля пользователя",
    security: [["bearerAuth" => []]],
    tags: ["user"])
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
            'data' => new UserResource($user->load([
                'watchedLectures:id,is_free,is_promo,title,preview_picture',
                'savedLectures:id,is_free,is_promo,title,preview_picture'
            ])),
        ]);
    }
}
