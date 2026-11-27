<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only allowlisted admins, authenticated either by the web session
 * (admin dashboard) or by an "admin" Sanctum token (admin mobile app).
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // The sanctum guard checks the web session first, then a bearer token.
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('admin.login'));
        }

        if (! self::isAdmin($user->email) || ! $user->tokenCan('admin')) {
            if (! $request->expectsJson()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();

                return redirect()->route('admin.login')->withErrors(['email' => 'This account is not an administrator.']);
            }

            return response()->json(['message' => 'Forbidden.'], 403);
        }

        Auth::setUser($user);

        return $next($request);
    }

    public static function isAdmin(?string $email): bool
    {
        return $email !== null && in_array(strtolower($email), config('admin.emails'), true);
    }
}
