<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\ProfilePhotoRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/user/photo',
    description: "Удаление всех фото юзера",
    summary: "Удалить фото юзера",
    security: [["bearerAuth" => []]],
    tags: ["user"])
]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthenticated'
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error'
)]
class PhotoDeleteController
{
    public function __construct(
        private UserService $service
    )
    {
    }

    public function __invoke(
//        ProfilePhotoRequest $request
    ): JsonResponse
    {
        $user = auth()->user();

        try {
            $this->service->deletePhoto($user);

        } catch (Exception $exception) {

            return response()->json([
                'data' => '',
                'message' => 'Что-то пошло не так: ' . $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Фото успешно удалены',
        ]);
    }
}
