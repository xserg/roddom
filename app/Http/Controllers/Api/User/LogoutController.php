<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/user/logout',
    description: "Логаут пользователя",
    summary: "Логаут пользователя",
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
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation exception',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]

class LogoutController
{
    public function __invoke(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Логаут пользователя прошел успешно'
        ], Response::HTTP_OK);
    }
}
