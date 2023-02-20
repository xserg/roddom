<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\LoginRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;


#[OA\Post(
    path: '/user/login',
    description: "Логин юзера с помощью почты и пароля",
    summary: "Логин юзера",
    tags: ["user"])
]
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
            )
        ]
    )]
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'access_token', type: 'string', example: '2|bNyLNAS0eqriGpH3O2z9bViYtBOtBk1bQKDIEifD'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
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

class LoginController
{
    public function __construct(
        private UserRepository $repository
    )
    {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $authenticated = Auth::attempt(
            $request->only(['email', 'password'])
        );

        if (!$authenticated) {
            $errors = [
                'message' => 'Email or password is invalid',
                'errors' => [
                    'email' => ['Email or password is invalid'],
                    'password' => ['Email or password is invalid']
                ]
            ];

            return response()->json(
                $errors,
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = $this->repository
            ->findByEmail(
                $request->input('email')
            );

        $token = $user
            ->createToken('access_token')
            ->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
