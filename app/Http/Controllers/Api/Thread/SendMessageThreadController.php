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

class SendMessageThreadController extends Controller
{
    public function __invoke(AddMessageToThreadRequest $request, Thread $thread)
    {
        Gate::authorize('add-message-to-thread', $thread);

        $messages = $thread->messages()->create([...$request->validated(), 'author_id' => auth()->id()]);

        Notification::make()
            ->title(function() use ($thread){
                return 'Новое сообщение в беседе';
            })
            ->body(fn() => 'С пользователем ' . auth()->user()->name ??  auth()->user()->email)
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
