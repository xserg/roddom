<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class RetrieveAllController
{
    public function __invoke(Request $req)
    {
        return ['check here' => $req->user()];
    }
}
