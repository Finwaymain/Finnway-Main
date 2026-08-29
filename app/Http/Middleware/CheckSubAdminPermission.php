<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubAdminPermission
{
    /**
     * Handle an incoming request for sub-admin permission validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin / Admin has 100% access to all routes
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Sub Admin check
        if ($user->isSubAdmin()) {
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Your Sub-Admin account has been deactivated by Admin.');
            }

            if ($user->hasPermission($permission)) {
                return $next($request);
            }

            return redirect()->route('dashboard')->with('error', 'Access Denied: You do not have permission to view this page.');
        }

        return $next($request);
    }
}
