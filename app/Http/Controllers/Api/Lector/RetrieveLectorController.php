<?php

namespace App\Http\Controllers\Api\Lector;

use App\Http\Resources\LectorResource;
use App\Models\Lector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lector/{id}',
    description: "Получение ресурса лектора",
    summary: "Получение ресурса лектора",
    security: [["bearerAuth" => []]],
    tags: ["lector"])
]
#[OA\Parameter(
    name: 'lector id',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectorResource'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error',
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
)]
class RetrieveLectorController
{
    public function __invoke(Request $request, int $id): JsonResource|JsonResponse
    {
        $lector = Lector::query()
            ->with(['diplomas:lector_id'])
            ->where(['id' => $id])
            ->first();

        if (!$lector) {
            return response()->json([
                'message' => 'Lector with id ' . $id . ' was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return LectorResource::make($lector);
    }
}
