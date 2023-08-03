<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Http\Resources\WizardResource;
use App\Models\Wizard;
use App\Models\WizardInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WizardControllerRetrieve extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(Request $request)
    {
        $wizardSteps = WizardResource::collection(Wizard::query()->orderBy('order')->get());
        $wizardCommonTitles = WizardInfo::query()->pluck('value', 'key');

        return response()->json([
            'common_titles' => $wizardCommonTitles,
            'data' => $wizardSteps,
        ]);
    }
}