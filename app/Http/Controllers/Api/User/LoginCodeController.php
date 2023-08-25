<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\LoginCodeExpiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\RefPointsGainOnce;
use App\Models\RefPointsPayments;
use App\Repositories\LoginCodeRepository;
use App\Repositories\UserRepository;
use App\Services\LoginCodeService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/user/login/code',
    description: 'Логин юзера с помощью почты и пароля',
    summary: 'Логин юзера',
    tags: ['user'])
]
#[OA\RequestBody(
    description: 'Code',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/LoginCodeRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/LoginCodeRequest')),
    ]
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'access_token', type: 'string', example: '2|bNyLNAS0eqriGpH3O2z9bViYtBOtBk1bQKDIEifD'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
    ]))]
class LoginCodeController extends Controller
{
    public function __construct(
        private LoginCodeRepository $loginCodeRepository,
        private LoginCodeService    $loginCodeService,
        private UserRepository      $userRepository,
        private UserService         $userService
    ) {
    }

    public function __invoke(LoginCodeRequest $request)
    {
        $code = $request->validated('code');

        $this->loginCodeService->throwIfExpired($code);

        $loginCode = $this->loginCodeRepository
            ->latestWhereCode($code);

        $user = $this->userRepository
            ->findByEmail(
                $loginCode->email,
                ['referrer.refPoints', 'refPoints']
            );

        $this->userService->rewardForRefLinkRegistration($user);

        $this->loginCodeService->deleteRecordsWithCode($code);

        $deviceName = $request->validated('device_name');
        $token = $this->userService->createToken($user, $deviceName);

        $user = $this->userService->appendLectureCountersToUser($user);

        Log::error("залогинили юзера $user->email, код был $code");

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
