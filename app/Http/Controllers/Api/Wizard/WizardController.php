<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Http\Resources\WizardResource;
use App\Models\Wizard;
use Illuminate\Http\Request;

class WizardController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(Request $request)
    {
        return WizardResource::collection(Wizard::query()->orderBy('order')->get());
    }
}
