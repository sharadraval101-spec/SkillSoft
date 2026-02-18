<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Log;

class RoleMiddleware
{
   public function handle(Request $request, Closure $next, $role)
{
    // 1. Ensure the user is logged in
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $userRole = (int)auth()->user()->role;

    // 2. If the user's role does NOT match the required role for this URL
    if ($userRole !== (int)$role) {
        // Redirect them to their own correct dashboard instead of showing 403
        return match($userRole) {
            \App\Models\User::ROLE_ADMIN    => redirect('/admin/dashboard'),
            \App\Models\User::ROLE_PROVIDER => redirect('/provider/dashboard'),
            default                         => redirect('/user/dashboard'),
        };
    }

    return $next($request);
}
}
