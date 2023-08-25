<?php

namespace App\Http\Controllers\Api\ResetPassword;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPassword\UpdatePasswordRequest;
use App\Services\PasswordResetService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/password/reset',
    description: 'Восстановление пароля, шаг третий, пользователь вводит пароль, подтверждение,
    еще нужен код (в скрытом поле)',
    summary: "Третий шаг функционала 'восстановление пароля' ",
    tags: ['reset-password'])
]
#[OA\RequestBody(
    description: 'код, пароль, подтверждение пароля',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/UpdatePasswordRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/UpdatePasswordRequest')),
    ]
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
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
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error'
)]
class ResetPasswordController extends Controller
{
    public function __construct(
        private UserService          $userService,
        private PasswordResetService $passwordResetService
    ) {
    }

    public function __invoke(
        UpdatePasswordRequest $request
    ): JsonResponse {
        $code = $request->input('code');
        $password = $request->input('password');

        $passwordReset = $this->passwordResetService->handleCodeExpiration($code);

        $user = $this->userService->getUserByEmail($passwordReset->email);
        $this->userService->updatePassword($user->id, $password);

        $passwordReset->delete();

        return response()->json([
            'message' => 'Пароль успешно обновлён',
        ], Response::HTTP_OK);
    }
}
