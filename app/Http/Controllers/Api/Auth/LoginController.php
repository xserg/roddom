<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\LoginRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    public function __construct(
        private UserRepository $repository
    )
    {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $authenticated = Auth::attempt(
            $request->only(['email', 'password'])
        );

        if (! $authenticated) {
            $errors = [
                'message' => 'Email or password is invalid',
                'errors' => [
                    'email' => ['Email or password is invalid'],
                    'password' => ['Email or password is invalid']
                ]
            ];

            return response()->json(
                $errors,
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = $this->repository
            ->findByEmail(
                $request->input('email')
            );

        $token = $user
            ->createToken('access_token')
            ->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
