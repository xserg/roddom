<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\ProfilePhotoRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/user/photo',
    description: "Обновление фото юзера",
    summary: "Загрузить фото юзера",
    tags: ["user"])
]
#[OA\RequestBody (
    description: "Photo file",
    required: true,
    content: [
        new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(title: 'photo', type: 'string', format: 'binary')
        ),
    ]
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'string'),
        new OA\Property(property: 'message', type: 'string'),
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
class PhotoController
{
    public function __construct(
        private UserService $service
    )
    {
    }

    public function __invoke(
        ProfilePhotoRequest $request
    ): JsonResponse
    {
        $user = auth()->user();
        $file = $request->file('photo');

        try {
            $path = $this->service->saveUsersPhoto($user, $file);
        } catch (Exception $exception){
            return response()->json([
                'data' => '',
                'message' => 'Something went wrong: ' . $exception->getMessage(),
            ]);
        }

        return response()->json([
            'data' => $path,
            'message' => 'Photo updated successfully',
        ]);
    }
}
