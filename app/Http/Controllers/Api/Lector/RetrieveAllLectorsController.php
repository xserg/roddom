<?php

namespace App\Http\Controllers\Api\Lector;

use App\Http\Resources\LectorResource;
use App\Models\Lector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Laravel\Sanctum\PersonalAccessToken;
use OpenApi\Attributes as OA;


#[OA\Get(
    path: '/lectors',
    description: "Получение ресурсов лекторов",
    summary: "Получение ресурсов лекторов",
    security: ["bearerAuth"],
    tags: ["lectors"]
),
]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectorResource'),
    ])
)]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveAllLectorsController
{
    public function __invoke(Request $req)
    {
        return LectorResource::collection(Lector::with('diplomas')->get());
    }
}
