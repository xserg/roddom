<?php

namespace App\Http\Controllers\Api\User;

use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/user/{id}',
    description: "Может сделать только залогиненный юзер",
    summary: "Подать заявку на удаление",
    security: ["bearerAuth"],
    tags: ["user"]
)]
#[OA\Parameter(
    parameter: 'id',
    name: 'user id',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
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
class DeleteUserController
{
    public function __construct(
        private readonly UserService $service
    )
    {
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->makeDeletionRequest($id);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Заявка на удаление аккаунта не зарегистрирована: ' . $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return response()->json([
            'message' => 'Заявка на удаление аккаунта зарегистрирована'
        ], Response::HTTP_OK);
    }
}
