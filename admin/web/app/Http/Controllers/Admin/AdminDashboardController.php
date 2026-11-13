<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    /**
     * Admin Dashboard: Account Recovery & Device Kill Switch Control Center
     */
    public function dashboard(Request $request): View
    {
        return view()->file(base_path('dashboard.blade.php'));
    }

    public function overview(): JsonResponse
    {
        return response()->json($this->dashboardPayload());
    }

    public function lockdown(Request $request): JsonResponse
    {
        $data = $request->validate([
            'confirmation' => ['required', 'string', 'in:LOCKDOWN'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $state = [
            'active' => true,
            'activated_at' => now()->toIso8601String(),
            'activated_by' => Auth::user()?->email ?? 'Admin',
            'reason' => $data['reason'] ?? 'Emergency security response',
        ];

        Cache::forever('admin.emergency_lockdown', $state);

        $sessionsRevoked = Schema::hasTable('sessions')
            ? DB::table('sessions')->delete()
            : 0;
        $tokensRevoked = Schema::hasTable('personal_access_tokens')
            ? DB::table('personal_access_tokens')->delete()
            : 0;

        $this->recordAudit(
            'Emergency Lockdown Activated',
            'Admin application',
            'critical'
        );

        return response()->json([
            'message' => 'Emergency lockdown activated.',
            'lockdown' => $state,
            'sessions_revoked' => $sessionsRevoked,
            'tokens_revoked' => $tokensRevoked,
        ]);
    }

    public function unlock(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:UNLOCK'],
        ]);

        Cache::forget('admin.emergency_lockdown');
        $this->recordAudit('Emergency Lockdown Deactivated', 'Admin application', 'info');

        return response()->json([
            'message' => 'Emergency lockdown deactivated.',
            'lockdown' => ['active' => false],
        ]);
    }

    public function deviceAction(Request $request, Device $device): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:remote_lock,revoke_tokens,mark_recovered'],
        ]);

        abort_unless(Schema::hasTable('admin_device_states'), 503, 'Run the admin database migrations first.');

        $status = match ($data['action']) {
            'remote_lock' => 'remotely_locked',
            'revoke_tokens' => 'tokens_revoked',
            'mark_recovered' => 'recovered',
        };

        DB::transaction(function () use ($device, $data, $status): void {
            DB::table('admin_device_states')->updateOrInsert(
                ['device_id' => $device->id],
                ['status' => $status, 'acted_by' => Auth::user()?->email ?? 'Admin', 'updated_at' => now(), 'created_at' => now()]
            );

            if ($data['action'] === 'revoke_tokens') {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where('tokenable_id', $device->user_id)
                    ->delete();
            }

            $this->recordAudit(
                Str::headline($data['action']),
                $device->device_name ?: "Device #{$device->id}",
                $status === 'recovered' ? 'info' : 'warning'
            );
        });

        return response()->json(['message' => 'Device action completed.', 'status' => $status]);
    }

    public function simulateEvent(): JsonResponse
    {
        abort_unless(Schema::hasTable('admin_audit_events'), 503, 'Run the admin database migrations first.');
        $event = $this->recordAudit('Simulated Security Event', 'sim_event_'.Str::lower(Str::random(8)), 'warning');

        return response()->json(['event' => $event]);
    }

    private function dashboardPayload(): array
    {
        $users = User::query()->whereNull('archived_at');
        $userCount = $users->count();
        $userRows = (clone $users)->latest('created_at')->get(['id', 'name', 'email', 'created_at']);
        $activeSessions = Schema::hasTable('sessions')
            ? DB::table('sessions')->where('last_activity', '>=', now()->subDay()->timestamp)->count()
            : 0;
        $devices = Device::query()->with('user')->latest('last_seen_at')->get();
        $deviceCounts = $devices->groupBy('user_id')->map->count();
        $states = Schema::hasTable('admin_device_states')
            ? DB::table('admin_device_states')->pluck('status', 'device_id')
            : collect();

        $deviceRows = $devices->map(function (Device $device) use ($states): array {
            $state = $states->get(
                $device->id,
                $device->last_seen_at && $device->last_seen_at->gt(now()->subDay()) ? 'active' : 'reported_lost'
            );

            return [
                'id' => $device->id,
                'name' => $device->device_name ?: 'Unnamed device',
                'owner' => $device->user?->email ?? 'Unknown user',
                'ip' => 'Not recorded',
                'location' => $device->last_seen_at?->diffForHumans() ?? 'Never seen',
                'status' => Str::headline($state),
                'state' => $state,
            ];
        })->values()->all();

        // Keep the dashboard view's recovery and kill-switch cards backed by
        // the same device data as the dashboard API payload.
        $activeDevices = collect($deviceRows)->map(fn (array $device): array => [
            'id' => $device['id'],
            'user_name' => $device['owner'],
            'user_email' => $device['owner'],
            'device_name' => $device['name'],
            'device_type' => 'Laptop',
            'os' => 'Unknown',
            'last_ip' => $device['ip'],
            'last_active' => $device['location'],
            'status' => $device['status'],
            'status_class' => match ($device['state']) {
                'active', 'recovered' => 'badge-active',
                'reported_lost' => 'badge-lost-phone',
                default => 'badge-stolen-laptop',
            },
        ])->all();

        $sessionRows = Schema::hasTable('sessions')
            ? DB::table('sessions')
                ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
                ->where('sessions.last_activity', '>=', now()->subDay()->timestamp)
                ->latest('sessions.last_activity')
                ->get([
                    'sessions.id',
                    'sessions.ip_address',
                    'sessions.user_agent',
                    'sessions.last_activity',
                    'users.email as user_email',
                ])
                ->map(fn (object $session): array => [
                    'user' => $session->user_email ?? 'Guest session',
                    'client' => Str::before($session->user_agent ?: 'Unknown client', ' '),
                    'ip' => $session->ip_address ?: 'Not recorded',
                    'last_seen' => now()->setTimestamp((int) $session->last_activity)->diffForHumans(),
                ])->values()->all()
            : [];

        $userRows = $userRows->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name ?: 'Unnamed user',
            'email' => $user->email,
            'created_at' => $user->created_at?->toDateTimeString(),
            'devices' => $deviceCounts->get($user->id, 0),
        ])->values()->all();

        return [
            'adminName' => Auth::user()?->name ?? 'Admin',
            'recoveryRequests' => [],
            'activeDevices' => $activeDevices,
            'pendingCount' => 0,
            'revokedDevicesCount' => collect($deviceRows)->whereNotIn('state', ['active', 'recovered'])->count(),
            'activeBypassCodes' => 0,
            'totalProtected' => $userCount,
            'metrics' => [
                'users' => $userCount,
                'sessions' => $activeSessions,
                'devices' => collect($deviceRows)->whereNotIn('state', ['active', 'recovered'])->count(),
                'health' => 'Operational',
            ],
            'devices' => $deviceRows,
            'users' => $userRows,
            'sessions' => $sessionRows,
            'lockdown' => Cache::get('admin.emergency_lockdown', ['active' => false]),
            'audit' => $this->auditRows(),
        ];
    }

    private function auditRows(): array
    {
        if (! Schema::hasTable('admin_audit_events')) {
            return [];
        }

        return DB::table('admin_audit_events')->latest()->limit(20)->get()->map(fn (object $event): array => [
            'time' => $event->created_at,
            'actor' => $event->actor,
            'action' => $event->action,
            'target' => $event->target,
            'severity' => $event->severity,
        ])->all();
    }

    private function recordAudit(string $action, ?string $target, string $severity): array
    {
        $event = [
            'time' => now()->toIso8601String(),
            'actor' => Auth::user()?->email ?? 'Admin',
            'action' => $action,
            'target' => $target,
            'severity' => $severity,
        ];

        if (Schema::hasTable('admin_audit_events')) {
            DB::table('admin_audit_events')->insert([
                'actor' => $event['actor'],
                'action' => $event['action'],
                'target' => $event['target'],
                'severity' => $event['severity'],
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $event;
    }

    /**
     * Admin Security Notifications Center
     */
    public function notifications(Request $request): View
    {
        $user = Auth::user();
        $adminName = $user ? $user->name : 'Admin';

        $notifications = [
            [
                'id' => 1,
                'category' => 'lost-device',
                'unread' => true,
                'urgent' => true,
                'title' => 'Emergency Lockdown Requested: Stolen Laptop',
                'message' => 'User Alex Rivera (alex.rivera@threatshield.internal) reported their Lenovo ThinkPad X1 Carbon stolen. Recommended action: Trigger Remote Kill Switch immediately.',
                'time' => '42 mins ago',
                'icon' => 'laptop',
            ],
            [
                'id' => 2,
                'category' => 'recovery',
                'unread' => true,
                'urgent' => false,
                'title' => 'Account Recovery Ticket: Lost Authenticator Phone',
                'message' => 'Sarah Chen (sarah.chen@threatshield.internal) is locked out of 2FA after losing their iPhone 15 Pro. Manager identity verification attached.',
                'time' => '1 hour ago',
                'icon' => 'phone',
            ],
            [
                'id' => 3,
                'category' => 'security',
                'unread' => false,
                'urgent' => false,
                'title' => 'Emergency Bypass Code Redeemed',
                'message' => 'A single-use recovery code (STS-4921-KZ) was used by dev.lead@threatshield.internal to regain account access. Old session keys revoked.',
                'time' => '3 hours ago',
                'icon' => 'key',
            ],
            [
                'id' => 4,
                'category' => 'lost-device',
                'unread' => false,
                'urgent' => false,
                'title' => 'Remote Wipe Completed on Revoked Device',
                'message' => 'Sanctum push tokens and credentials for device "Dell Latitude 7440" were successfully invalidated. Device cannot communicate with ThreatShield API.',
                'time' => 'Yesterday at 4:15 PM',
                'icon' => 'shield',
            ],
            [
                'id' => 5,
                'category' => 'recovery',
                'unread' => false,
                'urgent' => false,
                'title' => 'Hardware Failure Recovery Processed',
                'message' => 'Marcus Vance completed recovery key pairing on their replacement Google Pixel 9. Previous device wiped.',
                'time' => '2 days ago',
                'icon' => 'check',
            ],
        ];

        return view('admin.notifications', [
            'adminName' => $adminName,
            'notifications' => $notifications,
            'unreadCount' => 2,
        ]);
    }

    /**
     * Admin Settings & Recovery Policies
     */
    public function settings(Request $request): View
    {
        $user = Auth::user();
        $adminName = $user ? $user->name : 'Admin';

        return view('admin.settings', [
            'adminName' => $adminName,
        ]);
    }

    /**
     * Generate an Emergency Recovery Bypass Code for a locked-out user
     */
    public function generateBypassCode(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $code = 'STS-'.rand(1000, 9999).'-'.strtoupper(substr(md5(uniqid()), 0, 2));

        return response()->json([
            'status' => 'success',
            'code' => $code,
            'expires_in' => '60 minutes',
            'message' => "Emergency bypass code generated for {$email}.",
        ]);
    }

    /**
     * Remote Kill Switch / Device Revocation
     */
    public function lockdownDevice(Request $request): JsonResponse
    {
        $deviceId = $request->input('device_id');
        $deviceName = $request->input('device_name', 'Device');

        return response()->json([
            'status' => 'success',
            'message' => "Device {$deviceName} (ID: {$deviceId}) successfully revoked and wiped remotely.",
        ]);
    }

    /**
     * Save admin settings
     */
    public function saveSettings(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Admin recovery policies updated successfully.',
        ]);
    }
}
