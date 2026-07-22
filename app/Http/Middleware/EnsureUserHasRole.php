<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: EnsureUserHasRole::class . ':admin'
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // If the User model has a role attribute use it; otherwise deny.
        // Roles passed can be multiple; allow if user's role matches any of them or is 'admin'.
        if (! isset($user->role)) {
            abort(403, 'Unauthorized.');
        }

        if ($user->role !== 'admin' && ! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
