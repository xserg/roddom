<?php

namespace App\Http\Controllers\Api\Lector;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateLectorRequest;
use App\Jobs\UpdateAverageLectorRateJob;
use App\Models\Lector;
use App\Models\LectorRate;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/lector/{id}/rate',
    description: 'Оценить лектора',
    summary: 'Оценить лектора',
    security: [['bearerAuth' => []]],
    tags: ['lector'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лектора',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\RequestBody(
    description: 'рейт, передаваемый',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/RateLectorRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/RateLectorRequest')),
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
class RateLectorController extends Controller
{
    public function __invoke(
        RateLectorRequest $rateLectorRequest,
        int               $lectorId
    ) {
        $lector = Lector::findOrFail($lectorId);

        LectorRate::query()->updateOrCreate([
            'user_id' => auth()->id(),
            'lector_id' => $lectorId,
        ], ['rating' => $rateLectorRequest->validated('rate')]);

        dispatch(new UpdateAverageLectorRateJob($lector));

        return response()->json([
            'message' => 'Your rate was updated',
            'rates' => [
                'rate_user' => $rateLectorRequest->validated('rate'),
                'rate_avg' => $lector->averageRate?->rating,
            ],
        ], Response::HTTP_OK);
    }
}
