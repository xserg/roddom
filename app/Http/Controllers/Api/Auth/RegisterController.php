<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController
{
    public function __construct(
        private UserService $service
    )
    {
    }

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $attributes = [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ];

        $user = $this->service->create($attributes);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(
            [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], Response::HTTP_CREATED);
    }
}
