<?php

namespace App\Http\Controllers\Api\Thread;

use App\Filament\Resources\ThreadResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Thread\AddMessageToThreadRequest;
use App\Http\Resources\MessageResource;
use App\Models\Threads\Thread;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Filament\Notifications\Actions\Action;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/threads/{thread}',
    description: 'Создать сообщение в имеющемся обращении. Может юзер, создавший обращение и если это обращение имеет статус открыто',
    summary: 'Создание сообщения в обращении',
    security: [['bearerAuth' => []]],
    tags: ['threads'])
]
#[OA\RequestBody(
    description: 'Текст сообщения, который написал юзер',
    required: true,
    content: [
        new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/AddMessageToThreadRequest')),
        new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/AddMessageToThreadRequest')),
    ]
)]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data',
            description: 'Массив всех сообщений в обращении, сортировка по айди от меньшего к большему',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MessageResource')),
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
    ]),
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class SendMessageThreadController extends Controller
{
    public function __invoke(AddMessageToThreadRequest $request, Thread $thread)
    {
        Gate::authorize('add-message-to-thread', $thread);

        $thread->messages()->create([...$request->validated(), 'author_id' => auth()->id()]);

        Notification::make()
            ->title(function () use ($thread) {
                return 'Новое сообщение в беседе';
            })
            ->body(fn () => 'С пользователем ' . auth()->user()->getName())
            ->actions([
                Action::make('view')
                    ->label('Читать')
                    ->url(ThreadResource::getUrl('edit', ['record' => $thread->id, 'activeRelationManager' => 0]), true)
                    ->close()
                    ->button(),
            ])
            ->success()
            ->sendToDatabase(User::where('is_admin', true)->get());

        return MessageResource::collection($thread->messages);
    }
}
