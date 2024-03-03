<?php

namespace App\Http\Controllers\Api\CustomNotifications;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Put(
    path: '/notifications/read',
    summary: 'Помечает все уведомления как прочитанные',
    security: [['bearerAuth' => []]],
    tags: ['notifications'])
]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'Success',
    content: [new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Set notification read'),
        ]),
    ])
]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
class MarkNotificationReadController extends Controller
{
    public function __invoke()
    {
        auth()->user()->markNotificationRead();

        return response()->json(['message' => 'Set notification read']);
    }
}
