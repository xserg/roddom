<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Models\Threads\Thread;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/threads/{thread}',
    description: 'Повесить на обращение статус закрыто. Может юзер, создавший обращение и если это обращение имеет статус открыто',
    summary: 'Закрыть обращение',
    security: [['bearerAuth' => []]],
    tags: ['threads'])
]
#[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'No content')]
#[OA\Response(
    response: Response::HTTP_FORBIDDEN,
    description: 'Forbidden'
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: [
        new OA\JsonContent(properties: [
            new OA\Property(property: 'data', type: 'array', items: new OA\Items(),
                example: []),
            new OA\Property(property: 'message', type: 'string', example: 'Not found.'),
        ])
    ],
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class CloseThreadController extends Controller
{
    public function __invoke(Thread $thread)
    {
        Gate::authorize('close-thread', $thread);

        $thread->setStatusClosed();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
