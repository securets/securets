<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EmergencyLockdownMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $lockdown = Cache::get('admin.emergency_lockdown', ['active' => false]);

        if (($lockdown['active'] ?? false) && ! in_array($routeName, [
            'admin.api.overview',
            'admin.api.system.unlock',
        ], true)) {
            return response()->json([
                'message' => 'Emergency lockdown is active. Administrative actions are temporarily suspended.',
                'lockdown' => $lockdown,
            ], 423);
        }

        return $next($request);
    }
}
