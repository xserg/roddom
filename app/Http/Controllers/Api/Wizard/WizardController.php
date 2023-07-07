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
use App\Repositories\UserRepository;
use App\Services\LoginCodeService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class WizardController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(LoginCodeRequest $request)
    {
        return response()->json([
            Wizard::all()
        ], 200);
    }
}
