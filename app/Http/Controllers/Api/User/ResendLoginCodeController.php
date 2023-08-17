<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\FailedCreateLoginCodeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailResendLoginCodeRequest;
use App\Mail\SendLoginCode;
use App\Services\LoginCodeService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

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

        $code = mt_rand(100000, 999999);

        $this->loginCodeService->createAndSendEmail($email, $code);

        return response()->json([
            'message' => 'Код отослан на ваш email',
        ], Response::HTTP_OK);
    }
}
