<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Route-level role gate: `->middleware('role:admin')` or `'role:manager'`. */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $required = Role::tryFrom($role);

        if (! $required) {
            abort(500, "Unknown role [{$role}].");
        }

        if (! $request->user()?->hasRoleAtLeast($required)) {
            abort(403, 'You need the '.$required->label().' role for this area.');
        }

        return $next($request);
    }
}
