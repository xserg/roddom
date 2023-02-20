<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Client\Request;

class LogoutController
{
    public function __invoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }
}
