<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\FailedCreateLoginCodeException;
use App\Http\Requests\LoginRequest;
use App\Mail\SendLoginCode;
use App\Services\LoginCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/user/login',
    description: 'Логин юзера с помощью почты и пароля',
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
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation exception',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class LoginController
{
    public function __construct(
        private LoginCodeService $loginCodeService
    ) {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $authenticated = Auth::attempt(
            $request->only(['email', 'password'])
        );

        if (! $authenticated) {
            $errors = [
                'message' => 'Email or password is invalid',
                'errors' => [
                    'email' => ['Email or password is invalid'],
                    'password' => ['Email or password is invalid'],
                ],
            ];

            return response()->json(
                $errors,
                Response::HTTP_UNAUTHORIZED
            );
        }

        $email = $request->validated('email');
        $this->loginCodeService->deleteWhereEmail($email);
        $code = mt_rand(100000, 999999);

        try {
            $this->loginCodeService->createAndSendEmail($email, $code);

        } catch (FailedCreateLoginCodeException $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }



        if (! $sent) {
            return response()->json([
                'message' => 'Невозможно послать email с кодом логина',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Код отослан на ваш email',
        ], Response::HTTP_OK);
    }
}
