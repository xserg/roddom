<?php

namespace App\Http\Middleware;

use App\Exceptions\Custom\TinkoffVerfyIpException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyTinkoffIpMiddleware
{
    private array $whiteList = [
        '91.194.226.00/23'
    ];

    public function handle(Request $request, Closure $next)
    {
        foreach ($this->whiteList as $ip) {
            app('firewall')->whitelist($ip);
        }

        if (! app('firewall')->isWhitelisted($request->ip())) {
            Log::warning('--------ip-verify-----------');
            Log::warning($request->ip() . ' is not in tinkoff whitelist');
            Log::warning('--------ip-verify-----------end--');

            throw new TinkoffVerfyIpException();
        }

        return $next($request);
    }
}
