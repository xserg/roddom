<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Http\Resources\WizardResource;
use App\Models\Wizard;
use App\Models\WizardInfo;
use Illuminate\Http\Request;

class WizardController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(Request $request)
    {
        return response()->json([
            'common_titles' => WizardInfo::query()->pluck( 'value', 'key'),
            'data' => WizardResource::collection(Wizard::query()->orderBy('order')->get()),
        ]);
    }
}
