<?php

namespace App\Http\Controllers\Api\CustomNotifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomNotificationResource;
use App\Models\CustomNotification;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/notifications',
    description: 'Возвращает проперти "data" в которой либо массив всех нотификаций,
    либо если query p=latest, то последняя нотификация. Если нет нотификаций, то 200 и пустой массив на запрос всех
    и 404 и {data = [], message="Not found"} на запрос последней',
    summary: 'Дернуть все нотификации либо последнюю',
    security: [['bearerAuth' => []]],
    tags: ['notifications'])
]
#[OA\Parameter(
    name: 'p',
    description: 'Query string ?p=latest - возвращает последнюю нотификацию. Если нет ни одной нотификации, то 404 и пустой массив в data',
    in: 'query',
    required: false,
    example: 'latest',
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'Success',
    content: [new OA\JsonContent(
        oneOf: [
            new OA\Schema(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CustomNotificationResource'))
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/CustomNotificationResource', type: 'object')
            ])
        ],
    )])
]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
        new OA\Property(property: 'message', type: 'string', example: 'Not found.'),
    ])
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class RetrieveNotificationsController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->query('p') === 'latest') {
            $latestNotification = CustomNotification::latest()->firstOrFail();

            return CustomNotificationResource::make($latestNotification);
        }

        $notifications = CustomNotification::latest()->get();

        return CustomNotificationResource::collection($notifications);
    }
}
