<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailResendLoginCodeRequest;
use App\Services\LoginCodeService;
use Illuminate\Http\Response;

class ResendLoginCodeController extends Controller
{
    public function __construct(
        private LoginCodeService $loginCodeService,
    ) {
    }

    public function __invoke(EmailResendLoginCodeRequest $request)
    {
        $email = $request->validated('email');

        $this->loginCodeService->deleteWhereEmail($email);

        $this->loginCodeService->createAndSendEmail($email);

        return response()->json([
            'message' => 'Код отослан на ваш email',
        ], Response::HTTP_OK);
    }
}
