<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/user/register',
    description: "Регистрация нового юзера с помощью почты и пароля",
    summary: "Регистрация нового юзера",
    tags: ["user"])
]
#[OA\RequestBody (
    description: "Register credentials",
    required: true,
    content: [new OA\JsonContent(
        properties: [
            new OA\Property(
                property: "email",
                description: "Email пользователя",
                type: "string"
            ),
            new OA\Property(
                property: "password",
                description: "Пароль пользователя",
                type: "string",
                maxLength: 255,
                minLength: 6
            ),
            new OA\Property(
                property: "password_confirmation",
                description: "Подтверждение пароля",
                type: "string"
            ),
        ]
    )]
)]
#[OA\Response(response: 201, description: 'OK',
    content: [new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/UserResource'))],
)]
#[OA\Response(response: 422, description: 'Validation exception',
    content: [new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(response: 500, description: 'Server Error')]
class RegisterController
{
    public function __construct(
        private UserService $service
    )
    {
    }

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $attributes = [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ];

        try {
            $user = $this->service->create($attributes);
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(
            [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], Response::HTTP_CREATED);
    }
}
