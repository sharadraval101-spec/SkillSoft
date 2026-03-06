<?php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $requiredRole = $this->normalizeRequiredRole($role);
        if ($requiredRole === null) {
            abort(403, 'Invalid role middleware configuration.');
        }

        if ((int) $user->role === $requiredRole) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return match ((int) $user->role) {
            User::ROLE_ADMIN => redirect('/admin/dashboard'),
            User::ROLE_PROVIDER => redirect('/provider/dashboard'),
            default => redirect('/user/dashboard'),
        };
    }

    private function normalizeRequiredRole(string $role): ?int
    {
        if (is_numeric($role)) {
            return (int) $role;
        }

        return match ($role) {
            'admin' => User::ROLE_ADMIN,
            'provider' => User::ROLE_PROVIDER,
            'customer', 'user' => User::ROLE_CUSTOMER,
            default => null,
        };
    }
}
