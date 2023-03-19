<?php

namespace App\Http\Controllers\Api\ResetPassword;

use App\Exceptions\ResetCodeExpiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPassword\CodeRequest;
use App\Services\PasswordResetService;
use Exception;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/password/check',
    description: "Второй шаг функционала 'восстановление пароля', пользователь вводит шестизначный код, который ему
    прислали в email, для того чтобы перейти к следующему шагу - задать новый пароль на свой аккаунт",
    summary: "Восстановление пароля, шаг второй, пользователь вводит шестизначный код",
    tags: ["reset-password"])
]
#[OA\RequestBody (
    description: "Code",
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/CodeRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/CodeRequest')),
    ]
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'message', type: 'string'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Валидация кода не прошла: неправильный код или срок действия кода истёк
    (срок действия кода - час после его помещения в базу данных),
    Если срок действия истёк - код удаляется из базы данных, после этого надо юзера перенаправлять на первый шаг',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error'
)]
class CodeCheckController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    )
    {
    }

    public function __invoke(CodeRequest $request)
    {
        $requestCode = $request->input('code');

        try {
            $code = $this
                ->passwordResetService
                ->checkCodeIfExpired($requestCode);

        } catch (ResetCodeExpiredException $exception) {

            $this->passwordResetService->deleteCode($requestCode);

            return response()->json([
                'message' => $exception->getMessage()
            ], 422);

        } catch (Exception $exception) {

            return response()->json([
                'message' => $exception->getMessage()
            ], 422);
        }

        return response([
            'code' => $code,
            'message' => 'Код валидный'
        ], 200);
    }
}
