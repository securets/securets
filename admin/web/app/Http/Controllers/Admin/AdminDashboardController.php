<?php

namespace App\Http\Controllers\Admin;

use App\Admin\AdminPreferences;
use App\Admin\AdminTables;
use App\Admin\SubscriptionStats;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class AdminDashboardController extends Controller
{
    /** Admin policy keys stored in public.system_controls, with their defaults. */
    private const SETTINGS = [
        'admin.recovery.code_ttl_minutes' => '60',
        'admin.recovery.require_identity_verification' => '1',
        'admin.recovery.force_reenrollment' => '1',
        'admin.lockdown.revoke_tokens' => '1',
        'admin.lockdown.blacklist_hardware' => '1',
        'admin.alerts.email' => '',
        'admin.alerts.webhook_url' => '',
    ];

    private const NOTIFICATIONS_READ_KEY = 'admin.notifications.read_at';

    /**
     * Admin Dashboard: Account Recovery & Device Kill Switch Control Center
     */
    public function dashboard(Request $request, AdminPreferences $preferences): View
    {
        return view()->file(base_path('dashboard.blade.php'), [
            'preferences' => $preferences->get(Auth::id()),
            'preferenceOptions' => AdminPreferences::OPTIONS,
        ]);
    }

    /**
     * Full payload used by the admin mobile app. Keep its shape unchanged.
     */
    public function overview(): JsonResponse
    {
        return response()->json($this->dashboardPayload());
    }

    /**
     * Headline numbers for the web dashboard. The tables themselves load page
     * by page from /admin/api/tables/{table} (see AdminTableController).
     */
    public function summary(AdminTables $tables, SubscriptionStats $stats): JsonResponse
    {
        $database = $this->databaseHealth();
        $sessions = $tables->count('sessions');
        $flaggedDevices = $tables->count('devices');

        return response()->json([
            'adminName' => Auth::user()?->name ?? 'Admin',
            'metrics' => [
                'users' => User::query()->whereNull('archived_at')->count(),
                'sessions' => $sessions,
                'devices' => $flaggedDevices,
                'health' => $database['status'],
            ],
            'system' => ['database' => $database],
            'subscriptions' => $stats->summary(),
        ]);
    }

    public function savePreferences(Request $request, AdminPreferences $preferences): JsonResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'string', Rule::in(AdminPreferences::OPTIONS['theme'])],
            'accent' => ['required', 'string', Rule::in(AdminPreferences::OPTIONS['accent'])],
            'density' => ['required', 'string', Rule::in(AdminPreferences::OPTIONS['density'])],
            'start_page' => ['required', 'string', Rule::in(AdminPreferences::OPTIONS['start_page'])],
        ]);

        return response()->json([
            'message' => 'Your display preferences were saved.',
            'preferences' => $preferences->save((int) Auth::id(), $data),
        ]);
    }

    /**
     * Server-rendered admin sections (users, sessions, recovery, audit).
     */
    public function section(string $section): View
    {
        return view('admin.section', ['section' => $section] + $this->dashboardPayload());
    }

    public function status(): View
    {
        return view('admin.section', [
            'section' => 'status',
            'systemApi' => $this->systemApiHealth(),
            'systemDatabase' => $this->databaseHealth(),
        ]);
    }

    public function deviceAction(Request $request, Device $device): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:remote_lock,revoke_tokens,mark_recovered'],
        ]);

        return response()->json([
            'message' => 'Device action completed.',
            'status' => $this->applyDeviceAction($device, $data['action']),
        ]);
    }

    private function applyDeviceAction(Device $device, string $action): string
    {
        abort_unless(Schema::hasTable('admin_device_states'), 503, 'Run the admin database migrations first.');

        $status = match ($action) {
            'remote_lock' => 'remotely_locked',
            'revoke_tokens' => 'tokens_revoked',
            'mark_recovered' => 'recovered',
        };

        DB::transaction(function () use ($device, $action, $status): void {
            DB::table('admin_device_states')->updateOrInsert(
                ['device_id' => $device->id],
                ['status' => $status, 'acted_by' => Auth::user()?->email ?? 'Admin', 'updated_at' => now(), 'created_at' => now()]
            );

            if ($action === 'revoke_tokens') {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where('tokenable_id', $device->user_id)
                    ->delete();
            }

            $this->recordAudit(
                Str::headline($action),
                $device->device_name ?: "Device #{$device->id}",
                $status === 'recovered' ? 'info' : 'warning'
            );
        });

        return $status;
    }

    private function dashboardPayload(): array
    {
        $users = User::query()->whereNull('archived_at');
        $userCount = $users->count();
        $userRows = (clone $users)->latest('created_at')->get(['id', 'name', 'email', 'created_at']);
        $devices = Device::query()->with('user')->latest('last_seen_at')->get();
        $deviceCounts = $devices->groupBy('user_id')->map->count();
        $states = Schema::hasTable('admin_device_states')
            ? DB::table('admin_device_states')->pluck('status', 'device_id')
            : collect();
        $plans = DB::table('subscriptions')->pluck('plan', 'user_id');
        $appCounts = DB::table('integrations')
            ->where('is_active', true)
            ->groupBy('user_id')
            ->selectRaw('user_id, count(*) as total')
            ->pluck('total', 'user_id');
        $lastIps = DB::table('login_events')
            ->whereNotNull('ip_address')
            ->orderBy('created_at')
            ->pluck('ip_address', 'user_id');

        $deviceRows = $devices->map(function (Device $device) use ($states, $lastIps): array {
            $state = $states->get(
                $device->id,
                $device->last_seen_at && $device->last_seen_at->gt(now()->subDay()) ? 'active' : 'reported_lost'
            );

            return [
                'id' => $device->id,
                'name' => $device->device_name ?: 'Unnamed device',
                'owner' => $device->user?->email ?? 'Unknown user',
                'ip' => $lastIps->get($device->user_id, 'Not recorded'),
                'location' => $device->last_seen_at ? 'Last seen '.$device->last_seen_at->diffForHumans() : 'Never seen',
                'status' => Str::headline($state),
                'state' => $state,
            ];
        })->values();

        $flaggedDevices = $deviceRows->whereNotIn('state', ['active', 'recovered'])->count();

        $sessionRows = Schema::hasTable('sessions')
            ? DB::table('sessions')
                ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
                ->where('sessions.last_activity', '>=', now()->subDay()->timestamp)
                ->orderByDesc('sessions.last_activity')
                ->get([
                    'sessions.id',
                    'sessions.ip_address',
                    'sessions.user_agent',
                    'sessions.last_activity',
                    'users.email as user_email',
                ])
                ->map(fn (object $session): array => [
                    'user' => $session->user_email ?? 'Guest session',
                    'device' => $this->operatingSystem($session->user_agent),
                    'client' => $this->browser($session->user_agent),
                    'ip' => $session->ip_address ?: 'Not recorded',
                    'last_seen' => Carbon::createFromTimestamp((int) $session->last_activity)->diffForHumans(),
                ])->values()->all()
            : [];

        $userRows = $userRows->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name ?: 'Unnamed user',
            'email' => $user->email,
            'created_at' => $user->created_at?->toDateTimeString(),
            'plan' => Str::headline((string) $plans->get($user->id, 'free')),
            'apps' => (int) $appCounts->get($user->id, 0),
            'devices' => $deviceCounts->get($user->id, 0),
        ])->values()->all();

        $database = $this->databaseHealth();

        return [
            'adminName' => Auth::user()?->name ?? 'Admin',
            'pendingCount' => $flaggedDevices,
            'revokedDevicesCount' => $deviceRows->where('state', 'tokens_revoked')->count(),
            'totalProtected' => $userCount,
            'metrics' => [
                'users' => $userCount,
                'sessions' => count($sessionRows),
                'devices' => $flaggedDevices,
                'health' => $database['status'],
            ],
            'system' => ['database' => $database],
            'devices' => $deviceRows->all(),
            'users' => $userRows,
            'sessions' => $sessionRows,
            'audit' => $this->auditRows(),
        ];
    }

    /**
     * Admin actions plus Target App login events, newest first.
     */
    private function auditRows(int $limit = 20): array
    {
        $adminEvents = Schema::hasTable('admin_audit_events')
            ? DB::table('admin_audit_events')->latest()->limit($limit)->get()->map(fn (object $event): array => [
                'time' => $event->created_at,
                'actor' => $event->actor,
                'action' => $event->action,
                'target' => $event->target,
                'severity' => $event->severity,
            ])
            : collect();

        $loginEvents = DB::table('login_events')
            ->leftJoin('users', 'users.id', '=', 'login_events.user_id')
            ->leftJoin('integrations', 'integrations.id', '=', 'login_events.integration_id')
            ->latest('login_events.created_at')
            ->limit($limit)
            ->get([
                'login_events.created_at',
                'login_events.status',
                'login_events.credential_username',
                'users.email as user_email',
                'integrations.name as integration_name',
            ])
            ->map(fn (object $event): array => [
                'time' => $event->created_at,
                'actor' => $event->credential_username ?: ($event->user_email ?? 'Unknown user'),
                'action' => 'Login '.Str::headline($event->status),
                'target' => $event->integration_name ?? 'Unknown Target App',
                'severity' => in_array($event->status, ['denied', 'expired', 'blocked'], true) ? 'warning' : 'info',
            ]);

        return $adminEvents->concat($loginEvents)
            ->sortByDesc('time')
            ->take($limit)
            ->values()
            ->all();
    }

    private function recordAudit(string $action, ?string $target, string $severity): void
    {
        if (! Schema::hasTable('admin_audit_events')) {
            return;
        }

        DB::table('admin_audit_events')->insert([
            'actor' => Auth::user()?->email ?? 'Admin',
            'action' => $action,
            'target' => $target,
            'severity' => $severity,
            'metadata' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function databaseHealth(): array
    {
        $startedAt = microtime(true);

        try {
            DB::connection()->select('select 1');
            $status = 'Operational';
            $latency = (int) round((microtime(true) - $startedAt) * 1000);
        } catch (Throwable) {
            $status = 'Unavailable';
            $latency = null;
        }

        return ['status' => $status, 'latency_ms' => $latency, 'driver' => DB::connection()->getDriverName()];
    }

    private function systemApiHealth(): array
    {
        $endpoint = config('admin.system_url').'/up';
        $startedAt = microtime(true);

        try {
            $ok = Http::timeout(3)->acceptJson()->get($endpoint)->successful();
        } catch (Throwable) {
            $ok = false;
        }

        return [
            'status' => $ok ? 'Operational' : 'Unavailable',
            'latency_ms' => $ok ? (int) round((microtime(true) - $startedAt) * 1000) : null,
            'endpoint' => $endpoint,
        ];
    }

    private function operatingSystem(?string $userAgent): string
    {
        return match (true) {
            ! $userAgent => 'Unknown',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    private function browser(?string $userAgent): string
    {
        return match (true) {
            ! $userAgent => 'Unknown client',
            str_contains($userAgent, 'Expo') || str_contains($userAgent, 'okhttp') => 'Mobile app',
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => Str::limit($userAgent, 40),
        };
    }

    /**
     * Admin Security Notifications Center, built from flagged devices,
     * Target App login events, and admin audit events.
     */
    public function notifications(Request $request): View
    {
        $readAt = DB::table('system_controls')->where('key', self::NOTIFICATIONS_READ_KEY)->value('value');
        $readAt = $readAt ? Carbon::parse($readAt) : null;

        $payload = $this->dashboardPayload();

        $deviceAlerts = collect($payload['devices'])
            ->whereNotIn('state', ['active', 'recovered'])
            ->map(fn (array $device): array => [
                'category' => 'lost-device',
                'urgent' => $device['state'] === 'reported_lost',
                'title' => "{$device['status']}: {$device['name']}",
                'message' => "Device owned by {$device['owner']}. {$device['location']}.",
                'at' => null,
                'icon' => 'laptop',
            ]);

        $eventAlerts = collect($this->auditRows(50))->map(fn (array $event): array => [
            'category' => str_starts_with($event['action'], 'Login') ? 'security' : 'recovery',
            'urgent' => $event['severity'] === 'warning',
            'title' => $event['action'],
            'message' => trim("{$event['actor']} · {$event['target']}", ' ·'),
            'at' => $event['time'] ? Carbon::parse($event['time']) : null,
            'icon' => str_starts_with($event['action'], 'Login') ? 'key' : 'shield',
        ]);

        /** @var Collection<int, array<string, mixed>> $notifications */
        $notifications = $deviceAlerts->concat($eventAlerts)->values()->map(function (array $item, int $index) use ($readAt): array {
            $item['id'] = $index + 1;
            $item['unread'] = $item['at'] === null ? $readAt === null : ($readAt === null || $item['at']->gt($readAt));
            $item['time'] = $item['at']?->diffForHumans() ?? 'Open';

            return $item;
        });

        return view('admin.notifications', [
            'adminName' => $payload['adminName'],
            'notifications' => $notifications->all(),
            'unreadCount' => $notifications->where('unread', true)->count(),
            'categoryCounts' => $notifications->countBy('category')->all(),
        ]);
    }

    public function markNotificationsRead(): JsonResponse
    {
        DB::table('system_controls')->updateOrInsert(
            ['key' => self::NOTIFICATIONS_READ_KEY],
            ['value' => now()->toIso8601String(), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * Admin Settings & Recovery Policies, read from public.system_controls.
     */
    public function settings(Request $request): View
    {
        $stored = DB::table('system_controls')->whereIn('key', array_keys(self::SETTINGS))->pluck('value', 'key');

        return view('admin.settings', [
            'adminName' => Auth::user()?->name ?? 'Admin',
            'settings' => collect(self::SETTINGS)->map(fn (string $default, string $key) => $stored->get($key, $default))->all(),
        ]);
    }

    /**
     * Persist admin settings to public.system_controls.
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code_ttl_minutes' => ['required', 'integer', 'in:15,30,60,1440'],
            'require_identity_verification' => ['boolean'],
            'force_reenrollment' => ['boolean'],
            'revoke_tokens' => ['boolean'],
            'blacklist_hardware' => ['boolean'],
            'alerts_email' => ['nullable', 'email', 'max:255'],
            'alerts_webhook_url' => ['nullable', 'url', 'max:255'],
        ]);

        $values = [
            'admin.recovery.code_ttl_minutes' => (string) $data['code_ttl_minutes'],
            'admin.recovery.require_identity_verification' => $request->boolean('require_identity_verification') ? '1' : '0',
            'admin.recovery.force_reenrollment' => $request->boolean('force_reenrollment') ? '1' : '0',
            'admin.lockdown.revoke_tokens' => $request->boolean('revoke_tokens') ? '1' : '0',
            'admin.lockdown.blacklist_hardware' => $request->boolean('blacklist_hardware') ? '1' : '0',
            'admin.alerts.email' => (string) ($data['alerts_email'] ?? ''),
            'admin.alerts.webhook_url' => (string) ($data['alerts_webhook_url'] ?? ''),
        ];

        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                DB::table('system_controls')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $this->recordAudit('Admin Settings Updated', 'Recovery policies', 'info');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Admin recovery policies updated successfully.',
        ]);
    }

    /**
     * Remote Kill Switch: applies the real remote_lock device state.
     */
    public function lockdownDevice(Request $request): JsonResponse
    {
        $data = $request->validate(['device_id' => ['required', 'integer', 'exists:devices,id']]);
        $device = Device::query()->findOrFail($data['device_id']);
        $status = $this->applyDeviceAction($device, 'remote_lock');

        return response()->json([
            'status' => 'success',
            'device_status' => $status,
            'message' => 'Device '.($device->device_name ?: "#{$device->id}").' locked remotely.',
        ]);
    }
}
