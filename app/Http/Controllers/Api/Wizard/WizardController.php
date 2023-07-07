<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Exceptions\LoginCodeExpiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\RefPointsGainOnce;
use App\Models\RefPointsPayments;
use App\Models\Wizard;
use App\Repositories\LoginCodeRepository;

class WizardController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(\Illuminate\Http\Request $request)
    {
        return response()->json([
            Wizard::all()
        ], 200);
    }
}
