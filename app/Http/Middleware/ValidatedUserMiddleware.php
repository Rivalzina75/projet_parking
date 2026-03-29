<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidatedUserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->is_validated) {
            return redirect()->route('account.pending');
        }

        return $next($request);
    }
}
