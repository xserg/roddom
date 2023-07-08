<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Models\Wizard;
use Illuminate\Http\Request;

class WizardController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(Request $request)
    {
        return response()->json([
            'data' => Wizard::query()->orderBy('order')->get(['title', 'form'])
        ], 200);
    }
}
