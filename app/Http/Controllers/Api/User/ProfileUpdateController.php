<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/user/profile',
    description: "Обновление профиля пользователя",
    summary: "Обновление профиля пользователя",
    security: [["bearerAuth" => []]],
    tags: ["user"])
]
#[OA\RequestBody (
    description: "Данные профиля",
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ProfileRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/ProfileRequest')),
    ]
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
    ])
)]
#[OA\Response(
    response: 422,
    description: 'Validation exception',
    content: [
        new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: '#/components/schemas/ValidationErrors'))],
)]
#[OA\Response(response: 500, description: 'Server Error')]
class ProfileUpdateController
{
    public function __construct(
//        private UserService $service
    )
    {
    }

    public function __invoke(
        ProfileRequest $request
    ): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = auth()->user();
        $user->fill($request->input());

        if ($pregnancy_weeks = $request->pregnancy_weeks) {
            $user->pregnancy_start = Carbon::now()
                ->subWeeks($pregnancy_weeks)
                ->toDateString();
        }

        $user->save();

        return response()->json([
            'data' => UserResource::make($user),
        ]);
    }
}
