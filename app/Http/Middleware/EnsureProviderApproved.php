<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        if ((int) $user->role !== User::ROLE_PROVIDER) {
            return $next($request);
        }

        $profile = $user->providerProfile;
        $isApproved = $profile && $profile->status === 'active';

        if ($isApproved) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Provider account is pending admin approval.',
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Provider account is pending admin approval.');
    }
}

