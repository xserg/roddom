<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Get(path: '/register')]
#[OA\RequestBody (
    description: "Login credentials",
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
#[OA\Response(response: 200, description: 'AOK')]
#[OA\Response(response: 401, description: 'Not allowed')]
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

        $user = $this->service->create($attributes);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(
            [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], Response::HTTP_CREATED);
    }
}
