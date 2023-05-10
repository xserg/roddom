<?php

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use Illuminate\Http\Request;

class RetrieveAllUsersController
{
    public function __invoke(Request $req)
    {
        return ['users check' => User::all()];
    }
}
