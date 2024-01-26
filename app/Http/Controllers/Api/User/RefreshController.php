<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\Custom\RefreshTokenIsExpired;
use App\Http\Controllers\Controller;
use App\Http\Requests\RefreshRequest;
use App\Models\RefreshToken;
use App\Services\UserService;
use Symfony\Component\HttpFoundation\Response;

class RefreshController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function __invoke(RefreshRequest $request)
    {
        $refreshToken = RefreshToken::firstWhere('token', $request->validated('refresh_token'));

        if ($refreshToken->isExpired()) {
            throw new RefreshTokenIsExpired();
        }

        $user = $refreshToken->accessToken->tokenable;
        $deviceName = $refreshToken->accessToken->name;

        $accessToken = $this->userService->createAccessToken($user, $deviceName);
        $refreshToken = $this->userService->createRefreshToken($accessToken);

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->token,
        ], Response::HTTP_OK);
    }
}
