<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Return metrics and threat defense status for mobile dashboard.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'active',
            'timestamp' => now()->toIso8601String(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'metrics' => [
                'threats_blocked' => 1428,
                'active_shields' => 4,
                'network_security' => '99.8%',
                'system_health' => 'Optimal',
                'threat_level' => 'LOW',
            ],
            'recent_activity' => [
                ['id' => 1, 'event' => 'Firewall Rule Synchronized', 'time' => '10 mins ago', 'severity' => 'info'],
                ['id' => 2, 'event' => 'Unauthorized IP Blocked', 'time' => '25 mins ago', 'severity' => 'warning'],
                ['id' => 3, 'event' => 'Threat Intelligence DB Updated', 'time' => '1 hour ago', 'severity' => 'success'],
            ],
        ]);
    }
}
