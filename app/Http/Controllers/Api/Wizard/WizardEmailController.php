<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PregnancyFormRequest;
use App\Models\AppInfo;
use App\Notifications\PregnancyFormEmailNotification;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/pregnancy-plan-form',
    description: 'Принимаем html разметку - заполненую юзером форму с вопросами и ответами,
     засовываем в email и посылаем её юзеру на почту, на которую он зарегал свой аккаунт, а не ту,
     что он указывает в ответе на один из вопросов формы',
    summary: 'Принимаем разметку, засовываем в email и посылаем её юзеру',
    security: [['bearerAuth' => []]],
    tags: ['wizard'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message',
            type: 'string',
            example: 'email sent successfully')])
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class WizardEmailController extends Controller
{
    public function __invoke(PregnancyFormRequest $request)
    {
        $user = auth()->user();

        $html = $request->validated('data');
        $appInfo = AppInfo::first();
        $appLink = $appInfo?->app_link_share_link ??
            config('app.url');
        $appName = $appInfo?->app_title ??
            config('app.name');

        $user->notify(new PregnancyFormEmailNotification($html, $appLink, $appName));

        return response()->json(['message' => 'email sent successfully'], Response::HTTP_OK);
    }
}
