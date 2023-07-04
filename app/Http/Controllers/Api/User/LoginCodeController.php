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

        try {
            $this
                ->loginCodeService
                ->throwIfExpired($code);

        } catch (LoginCodeExpiredException $exception) {

            $this->loginCodeService->deleteRecordsWithCode($code);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);

        } catch (Exception $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $loginCode = $this
            ->loginCodeRepository
            ->latestWhereCode($code);

        $user = $this
            ->userRepository
            ->findByEmail(
                $loginCode->email,
                ['referrer.refPoints', 'refPoints']
            );

        if ($user->hasReferrer()) {
            if (! $user->referrer->hasVerifiedEmail()) {
                $referrer = $user->referrer;
                $pointsToGet = RefPointsGainOnce::query()->firstWhere('user_type', 'referrer')?->points_gains ?? 0;
                $referrer->refPointsGetPayments()->create([
                    'payer_id' => $user->id,
                    'ref_points' => $pointsToGet,
                    'reason' => RefPointsPayments::REASON_INVITE
                ]);

                if ($refPoints = $referrer->refPoints) {
                    $refPoints->points += $pointsToGet;
                    $refPoints->save();
                } else {
                    $referrer->refPoints()->updateOrCreate(['points' => $pointsToGet]);
                }

                $referrer->markEmailAsVerified();
            }

            if ($user->canGetReferralsBonus()) {
                $pointsToGet = RefPointsGainOnce::query()->firstWhere('user_type', 'referral')?->points_gains ?? 0;
                if ($refPoints = $user->refPoints) {
                    $refPoints->points += $pointsToGet;
                    $refPoints->save();
                } else {
                    $user->refPoints()->updateOrCreate(['points' => $pointsToGet]);
                }

                $user->refPointsGetPayments()->create([
                    'payer_id' => $referrer->id,
                    'ref_points' => $pointsToGet,
                    'reason' => RefPointsPayments::REASON_INVITED
                ]);

                $user->markCantGetReferralsBonus();
            }
        }

        $this->loginCodeService->deleteRecordsWithCode($code);
        $user->tokens()->delete();

        $token = $user
            ->createToken('access_token')
            ->plainTextToken;

        $user = $this->userService->appendLectureCountersToUser($user);

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
