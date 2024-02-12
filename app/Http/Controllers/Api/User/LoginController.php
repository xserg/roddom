<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/user/login',
    description: 'Логин юзера с помощью почты и пароля. Удаляется предыдущий код логина, создаётся новый и посылается
    на мыло юзеру.',
    summary: 'Логин юзера',
    tags: ['user'])
]
#[OA\RequestBody(
    description: 'Login credentials',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/LoginRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/LoginRequest')),
    ]
)]
#[OA\Response(response: Response::HTTP_OK, description: 'OK')]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Validation exception',
    content: [new OA\MediaType(
        mediaType: 'application/json',
        schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors')),],
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class LoginController
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $authenticated = $this->userService->login($request->getDto());

        if (! $authenticated) {
            $errors = [
                'message' => 'Email or password is invalid',
                'errors' => [
                    'email' => ['Email or password is invalid'],
                    'password' => ['Email or password is invalid'],
                ],
            ];

            return response()->json($errors, Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(['message' => 'Код отослан на ваш email'], Response::HTTP_OK);
    }
}
