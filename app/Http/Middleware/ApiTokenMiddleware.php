<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $expected = config('services.api.token');

        if (empty($expected)) {
            return response('API token not configured', 500);
        }

        if (! hash_equals($expected, (string) $token)) {
            return response('Unauthorized', 401);
        }

        return $next($request);
    }
}
