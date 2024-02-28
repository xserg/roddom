<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Models\Threads\Thread;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/threads',
    description: 'Юзер создает обращение',
    summary: 'Создание обращения',
    security: [['bearerAuth' => []]],
    tags: ['threads'])
]
#[OA\Response(response: Response::HTTP_CREATED, description: 'Created',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'id', type: 'integer'),
    ])
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class CreateThreadController extends Controller
{
    public function __invoke()
    {
        $thread = Thread::create();
        auth()->user()->participants()->updateOrCreate(['thread_id' => $thread->id], ['opened' => true]);

        return response()->json(['id' => $thread->id], 201);
    }
}
