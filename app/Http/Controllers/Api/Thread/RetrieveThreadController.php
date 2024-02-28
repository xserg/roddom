<?php

namespace App\Http\Controllers\Api\Thread;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThreadResource;
use App\Models\Threads\Thread;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/threads/{thread}',
    description: 'Объект одного обращения по айди. Может юзер, создавший обращение.',
    summary: 'Получение одного обращения',
    security: [['bearerAuth' => []]],
    tags: ['threads'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/ThreadResource', type: 'object'),
    ])
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Forbidden')]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
        new OA\Property(property: 'message', type: 'string', example: 'Not found.'),
    ])
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class RetrieveThreadController extends Controller
{
    public function __invoke(Thread $thread)
    {
        Gate::authorize('show-thread-messages', $thread);

        $thread->participantForUser(auth()->id())->setReadAtNow();
        $thread = $thread->load(['participants', 'messages.author'])->append('messages');

        return ThreadResource::make($thread);
    }
}
