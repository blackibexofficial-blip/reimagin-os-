<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the whole admin panel: the user must be signed in, active, and hold
 * at least the Staff role. Deactivating an account therefore revokes panel
 * access immediately, without deleting the audit trail.
 */
class EnsureAdminPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Authentication required.'], 401)
                : redirect()->guest(route('login'));
        }

        if (! $user->is_active) {
            abort(403, 'This account has been deactivated. Contact the studio owner.');
        }

        if (! $user->hasRoleAtLeast(\App\Enums\Role::Staff)) {
            abort(403, 'Your role does not grant admin access.');
        }

        return $next($request);
    }
}
