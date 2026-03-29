<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockUnvalidatedAccountMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->is_validated) {
            return $next($request);
        }

        if ($request->routeIs('account.pending') || $request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('account.pending');
    }
}
