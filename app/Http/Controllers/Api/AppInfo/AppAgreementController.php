<?php

namespace App\Http\Controllers\Api\AppInfo;

use App\Http\Controllers\Controller;
use App\Models\AppInfo;
use App\Models\Period;
use App\Models\RefInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AppAgreementController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appInfo = AppInfo::select(['agreement_title', 'agreement_text'])->first();

        return response()->json([
            'data' => [
                $appInfo
            ],
        ]);
    }
}
