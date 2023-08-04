<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateLectureRequest;
use App\Models\Lecture;
use App\Models\LectureRate;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/lecture/{id}/rate',
    description: 'Оценить лекцию',
    summary: 'Оценить лекцию',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\RequestBody(
    description: 'рейт, передаваемый',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/RateLectureRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/RateLectureRequest')),
    ]
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Unauthenticated'
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ])
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ])
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', example: 'Your rate was updated'),
        new OA\Property(property: 'rates', example: [
            'rate_user' => 5,
            'rate_avg' => '3.2525',
        ]),
    ]),
)]
class RateLectureController extends Controller
{
    public function __invoke(
        RateLectureRequest $rateLectureRequest,
        int                $lectureId
    ) {
        Lecture::findOrFail($lectureId);

        LectureRate::query()->updateOrCreate([
            'user_id' => auth()->id(),
            'lecture_id' => $lectureId,
        ], ['rating' => $rateLectureRequest->validated('rate')]);

        $rateAverage = LectureRate::query()
            ->where('lecture_id', $lectureId)
            ->average('rating');

        return response()->json([
            'message' => 'Your rate was updated',
            'rates' => [
                'rate_user' => $rateLectureRequest->rate,
                'rate_avg' => $rateAverage,
            ],
        ], Response::HTTP_OK);
    }
}
