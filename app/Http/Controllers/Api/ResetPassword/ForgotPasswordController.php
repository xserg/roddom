<?php

namespace App\Http\Controllers\Api\ResetPassword;

use App\Exceptions\FailedCreateResetCodeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPassword\ForgotPasswordRequest;
use App\Mail\SendCodeResetPassword;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/password/forgot',
    description: "Первый шаг функционала 'восстановление пароля'. Пользователь вводит свой email, на него отправляется шестизначный код",
    summary: "Восстановление пароля, шаг первый, пользователь вводит свой email",
    tags: ["reset-password"])
]
#[OA\RequestBody (
    description: "Forgot password, user email required",
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ForgotPasswordRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/ForgotPasswordRequest')),
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
class ForgotPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    )
    {
    }

    public function __invoke(
        ForgotPasswordRequest $request
    ): JsonResponse
    {
        $email = $request->input('email');
        $this->passwordResetService->deleteWhereEmail($email);

        $code = mt_rand(100000, 999999);

        try {
            $passwordReset = $this->passwordResetService->create($email, $code);
        } catch (FailedCreateResetCodeException $exception) {

            return response()->json([
                'message' => $exception->getMessage()
            ], 500);
        }

//        SendResetPasswordCodeJob::dispatch(
//            $request->email,
//            $passwordReset->code
//        );

        $sent = Mail
            ::to($email)
            ->send(new SendCodeResetPassword($passwordReset->code));

        if (!$sent) {
            return response()->json([
                'message' => 'Невозможно отослать код на email'
            ], 422);
        }

        return response()->json([
            'message' => 'Код отослан на ваш email'
        ], 200);
    }
}
