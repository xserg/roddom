<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\RegisterRequest;
use App\Mail\SendLoginCode;
use App\Services\LoginCodeService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
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
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/RegisterRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/RegisterRequest')),
    ]
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
        private UserService      $userService,
        private LoginCodeService $loginCodeService
    )
    {
    }

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $email = $request->email;
        $password = $request->password;

        try {
            $user = $this->userService->create(compact('email', 'password'));
            $code = mt_rand(100000, 999999);
            $this->loginCodeService->create($email, $code);

        } catch (\Exception $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $sent = Mail::to($email)
            ->send(new SendLoginCode($code));

        if (!$sent) {
            return response()->json([
                'message' => 'Невозможно послать email с кодом логина'
            ], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Код отослан на ваш email',
        ], Response::HTTP_OK);
    }
}
