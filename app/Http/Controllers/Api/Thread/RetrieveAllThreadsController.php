<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThreadResource;
use App\Models\Threads\Thread;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/threads',
    description: 'Массив всех обращений',
    summary: 'Получение всех обращений',
    security: [['bearerAuth' => []]],
    tags: ['threads'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ThreadResource')),
    ])
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class RetrieveAllThreadsController extends Controller
{
    public function __invoke()
    {
        $threads = Thread
            ::whereHas('participants', fn ($query) => $query->where('user_id', auth()->id()))
            ->with(['participants', 'messages.author'])
            ->get()
            ->append('messages');

        return ThreadResource::collection($threads);
    }
}
