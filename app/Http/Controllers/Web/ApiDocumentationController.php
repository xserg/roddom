<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    use AuthenticatesUsers;

    public string $redirectTo = '/api-docs';

    public function showLoginForm()
    {
        return view('api-documentation.login');
    }

    public function docs(Request $request)
    {
        if (
            $request->user() &&
            $request->user()->isAdmin()
        ) {
            return view('api-documentation.docs');
        }

        $this->logout($request);

        return redirect()->route('api-docs.login');
    }
}
