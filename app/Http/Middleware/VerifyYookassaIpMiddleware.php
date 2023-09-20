<?php

namespace App\Http\Middleware;

use App\Exceptions\Custom\YookassaVerfyIpException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyYookassaIpMiddleware
{
    private array $whiteList = [
        '185.71.76.0/27',
        '185.71.77.0/27',
        '77.75.153.0/25',
        '77.75.156.11',
        '77.75.156.35',
        '77.75.154.128/25',
        '2a02:5180::/32',
    ];

    public function handle(Request $request, Closure $next)
    {
        foreach ($this->whiteList as $ip) {
            app('firewall')->whitelist($ip);
        }

        if (! app('firewall')->isWhitelisted($request->ip())) {
            Log::warning('--------ip-verify-----------');
            Log::warning($request->ip() . ' is not in yookassa whitelist');
            Log::warning('--------ip-verify-----------end--');

            throw new YookassaVerfyIpException();
        }

        return $next($request);
    }
}
