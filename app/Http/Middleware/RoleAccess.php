<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var Account|null $user */
        $user = Auth::user();

        if (!($user instanceof Account)) {
            return redirect()->route('login');
        }

        $allowedRoles = array_map(fn ($role) => strtoupper(trim($role)), $roles);
        $currentRole = strtoupper(trim((string) $user->Role));

        if (! in_array($currentRole, $allowedRoles, true)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
