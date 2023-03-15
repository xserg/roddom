<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Lecture;
use App\Repositories\LectureRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/user/login',
    description: "Логин юзера с помощью почты и пароля",
    summary: "Логин юзера",
    tags: ["user"])
]
#[OA\RequestBody (
    description: "Login credentials",
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/LoginRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/LoginRequest')),
    ]
)]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'access_token', type: 'string', example: '2|bNyLNAS0eqriGpH3O2z9bViYtBOtBk1bQKDIEifD'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
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
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]

class LoginController
{
    public function __construct(
        private UserRepository $userRepository,
        private LectureRepository $lectureRepository,
        private UserService $userService
    )
    {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $authenticated = Auth::attempt(
            $request->only(['email', 'password'])
        );

        if (!$authenticated) {
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

        $user = $this->userRepository
            ->findByEmail(
                $request->input('email')
            );

        $user->tokens()->delete();

        $token = $user
            ->createToken('access_token')
            ->plainTextToken;

        $user = $this->userService->appendLectureCountersToUser($user);

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
