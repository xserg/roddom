<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PregnancyFormRequest;
use App\Http\Resources\WizardResource;
use App\Models\AppInfo;
use App\Models\Wizard;
use App\Models\WizardInfo;
use App\Notifications\PregnancyFormEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WizardEmailController extends Controller
{
    public function __construct()
    {
    }

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
