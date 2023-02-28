<?php

namespace App\Http\Controllers\Api\ResetPassword;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPassword\UpdatePasswordRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: 'password/reset',
    description: "Третий шаг функционала 'восстановление пароля'",
    summary: "Восстановление пароля, шаг третий, пользователь вводит пароль, подтверждение,
    еще нужен код (в скрытом поле)",
    tags: ["reset-password"])
]
#[OA\RequestBody (
    description: "код, пароль, подтверждение пароля",
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/UpdatePasswordRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/UpdatePasswordRequest')),
    ]
)]
class ResetPasswordController extends Controller
{
    public function __construct(
        private UserService $userService
    )
    {
    }

    public function __invoke(
        UpdatePasswordRequest $request
    ): JsonResponse
    {
        try {
            $this->userService->updateUsersPassword(
                $request->code,
                $request->password
            );
        } catch (Exception $exception) {

            return response()->json([
                'message' => $exception->getMessage()
            ], 422);
        }

        return response()->json([
            'message' => 'Пароль успешно обновлён'
        ], 200);
    }
}
