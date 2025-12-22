<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsMao
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isMao()) {
            abort(403);
        }

        return $next($request);
    }
}
