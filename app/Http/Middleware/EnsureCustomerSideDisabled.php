<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerSideDisabled
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role !== User::ROLE_CUSTOMER) {
            return $next($request);
        }

        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'User side has been removed. Please use an admin or provider account.',
            ], 403);
        }

        return redirect()
            ->route('login')
            ->with('error', 'User side has been removed. Please use an admin or provider account.');
    }
}
