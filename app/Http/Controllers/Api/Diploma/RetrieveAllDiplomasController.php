<?php

namespace App\Http\Controllers\Api\Diploma;

use App\Http\Resources\DiplomaCollection;
use App\Models\Diploma;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

//#[OA\Get(
//    path: '/diplomas',
//    description: "Получение ресурсов дипломов",
//    summary: "Получение ресурсов дипломов",
//    security: [["bearerAuth" => []]],
//    tags: ["diploma"])
//]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/DiplomaResource'),
    ])
)]
#[OA\Response(response: 500, description: 'Server Error')]

class RetrieveAllDiplomasController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            new DiplomaCollection(
                Diploma::all()
            ), Response::HTTP_OK
        );
    }
}
