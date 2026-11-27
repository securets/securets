@php
    $pages = [
        'users' => ['User Management', 'Registered accounts, subscription tiers, and Target App integrations'],
        'sessions' => ['Active Sessions', 'Authenticated web sessions and paired mobile instances'],
        'recovery' => ['Lost Device Recovery', 'Pending instance token revocations'],
        'audit' => ['Security Audit Trail', 'Live authentication and verification stream'],
        'status' => ['Global System Status', 'Authentication, database, and emergency enforcement health'],
    ];
    [$pageTitle, $pageDescription] = $pages[$section];
@endphp
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — SecureTS Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-text font-sans antialiased min-h-screen flex">
    <aside class="w-64 bg-surface border-r border-border flex flex-col justify-between shrink-0">
        <div class="p-6 flex flex-col gap-8">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="SecureTS dashboard">
                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center font-semibold text-sm tracking-tight">TS</div>
                <span class="font-semibold text-base tracking-tight">SecureTS</span>
                <span class="text-xs font-medium text-text-muted px-2 py-0.5 rounded-lg border border-border">Admin</span>
            </a>
            <nav aria-label="Main Navigation" class="flex flex-col gap-1">
                @foreach ([
                    'dashboard' => ['Dashboard', 'admin.dashboard'],
                    'users' => ['User Management', 'admin.users'],
                    'sessions' => ['Active Sessions', 'admin.sessions'],
                    'recovery' => ['Lost Device Recovery', 'admin.recovery'],
                    'audit' => ['Security Audit', 'admin.audit'],
                    'status' => ['System Status', 'admin.status'],
                ] as $key => [$label, $routeName])
                    <a href="{{ route($routeName) }}" @class([
                        'flex items-center gap-3 px-3 py-2.5 min-h-11 rounded-lg font-medium text-sm transition-colors duration-150 ease-out',
                        'bg-primary text-text' => $section === $key,
                        'text-text-muted hover:text-text hover:bg-background' => $section !== $key,
                    ])>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        <div class="p-6 border-t border-border text-sm text-text-muted flex flex-col gap-2">
            <span>{{ auth()->user()?->email }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="min-h-11 text-xs text-text-muted hover:text-text">Sign out</button></form>
        </div>
    </aside>

    <main class="flex-1 p-8 flex flex-col gap-8 overflow-y-auto">
        <header class="flex items-center justify-between border-b border-border pb-6">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-text">{{ $pageTitle }}</h1>
                <p class="text-sm text-text-muted mt-1">{{ $pageDescription }}</p>
            </div>
            <span class="text-xs font-mono tabular-nums text-text-muted px-3 py-2 rounded-lg bg-surface border border-border">Database: {{ ($systemDatabase ?? $system['database'])['status'] }}</span>
        </header>

        @if ($section === 'status')
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6" aria-label="Global System Status">
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col justify-between gap-4">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-text-muted">System API</span><span class="text-xs font-medium {{ $systemApi['status'] === 'Operational' ? 'text-accent' : 'text-red-400' }}">● {{ $systemApi['status'] }}</span></div>
                    <div class="flex items-baseline justify-between"><span class="text-2xl font-semibold font-mono text-text">{{ $systemApi['latency_ms'] !== null ? $systemApi['latency_ms'].' ms' : '—' }}</span><span class="text-xs text-text-muted">{{ $systemApi['endpoint'] }}</span></div>
                </div>
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col justify-between gap-4">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-text-muted">System Database</span><span class="text-xs font-medium {{ $systemDatabase['status'] === 'Operational' ? 'text-accent' : 'text-red-400' }}">● {{ $systemDatabase['status'] }}</span></div>
                    <div class="flex items-baseline justify-between"><span class="text-2xl font-semibold font-mono text-text">{{ $systemDatabase['latency_ms'] !== null ? $systemDatabase['latency_ms'].' ms' : '—' }}</span><span class="text-xs text-text-muted">{{ strtoupper($systemDatabase['driver']) }} · SELECT 1</span></div>
                </div>
            </section>
        @elseif ($section === 'recovery')
            @php($pendingDevices = collect($devices)->whereNotIn('state', ['active', 'recovered']))
            <section class="grid grid-cols-1 xl:grid-cols-3 gap-6" aria-label="Lost Device Recovery">
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6 xl:col-span-3">
                    <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">Lost Device Recovery</h2><p class="text-xs text-text-muted mt-0.5">Pending instance token revocations</p></div><span class="text-xs font-mono font-semibold text-accent px-2.5 py-1 rounded-lg border border-border">{{ $pendingDevices->count() }} Pending</span></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse ($pendingDevices as $device)
                            <div class="p-4 rounded-lg bg-background border border-border flex flex-col gap-3"><div class="flex items-start justify-between"><div><p class="text-sm font-medium text-text">{{ $device['owner'] }}</p><p class="text-xs text-text-muted mt-0.5">{{ $device['name'] }} · {{ $device['status'] }}</p></div><span class="text-xs font-mono text-text-muted">{{ $device['location'] }}</span></div><p class="text-xs font-mono text-text-muted">{{ $device['ip'] }}</p></div>
                        @empty
                            <p class="text-sm text-text-muted md:col-span-3">No devices require recovery action.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        @elseif ($section === 'sessions')
            <section class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6" aria-label="Active Sessions">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">Active Sessions</h2><p class="text-xs text-text-muted mt-0.5">Authenticated web sessions and paired mobile instances</p></div><span class="text-xs text-text-muted font-mono">{{ count($sessions) }} active in the last 24 hours</span></div>
                <div class="overflow-x-auto"><table class="w-full text-left border-collapse"><thead><tr class="border-b border-border text-xs font-medium text-text-muted"><th class="pb-3 pr-4">User</th><th class="pb-3 px-4">OS / Device</th><th class="pb-3 px-4">Browser / Client</th><th class="pb-3 px-4">IP Address</th><th class="pb-3 pl-4 text-right">Last Seen</th></tr></thead><tbody class="divide-y divide-border text-sm">
                    @forelse ($sessions as $session)
                        <tr><td class="py-4 pr-4 font-medium text-text">{{ $session['user'] }}</td><td class="py-4 px-4 text-text-muted">{{ $session['device'] }}</td><td class="py-4 px-4 text-text-muted">{{ $session['client'] }}</td><td class="py-4 px-4 font-mono text-xs text-text-muted">{{ $session['ip'] }}</td><td class="py-4 pl-4 text-right text-xs text-text-muted">{{ $session['last_seen'] }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-text-muted">No active sessions in the last 24 hours.</td></tr>
                    @endforelse
                </tbody></table></div>
            </section>
        @elseif ($section === 'audit')
            <section class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6" aria-label="Security Audit Trail">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">Security Audit Trail</h2><p class="text-xs text-text-muted mt-0.5">Live authentication and verification stream</p></div><span class="text-xs font-mono text-text-muted">{{ count($audit) }} latest events</span></div>
                <div class="flex flex-col gap-4">
                    @forelse ($audit as $event)
                        <div class="p-4 rounded-lg bg-background border {{ $event['severity'] === 'warning' ? 'border-accent' : 'border-border' }} flex flex-col gap-2"><div class="flex items-center justify-between"><span class="text-xs font-semibold text-text">{{ $event['action'] }}</span><span class="text-xs font-mono text-text-muted">{{ $event['time'] }}</span></div><p class="text-xs text-text-muted">{{ $event['actor'] }} · {{ $event['target'] ?? '—' }}</p></div>
                    @empty
                        <p class="text-sm text-text-muted">No audit events recorded yet.</p>
                    @endforelse
                </div>
            </section>
        @elseif ($section === 'users')
            <section class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6" aria-label="User Management">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">User Management</h2><p class="text-xs text-text-muted mt-0.5">Registered accounts, subscription tiers, and Target App integrations</p></div><span class="text-xs font-mono text-text-muted">{{ count($users) }} accounts</span></div>
                <div class="overflow-x-auto"><table class="w-full text-left border-collapse"><thead><tr class="border-b border-border text-xs font-medium text-text-muted"><th class="pb-3 pr-4">Account</th><th class="pb-3 px-4">Subscription Tier</th><th class="pb-3 px-4">Connected Apps</th><th class="pb-3 px-4">Paired Devices</th><th class="pb-3 pl-4 text-right">Joined</th></tr></thead><tbody class="divide-y divide-border text-sm">
                    @forelse ($users as $user)
                        <tr><td class="py-4 pr-4"><div class="font-medium text-text">{{ $user['name'] }}</div><div class="text-xs text-text-muted">{{ $user['email'] }}</div></td><td class="py-4 px-4"><span class="px-2.5 py-1 rounded-lg border border-border text-xs font-medium text-text">{{ $user['plan'] }}</span></td><td class="py-4 px-4 font-mono text-text">{{ $user['apps'] }}</td><td class="py-4 px-4 font-mono text-text-muted">{{ $user['devices'] }} {{ Str::plural('device', $user['devices']) }}</td><td class="py-4 pl-4 text-right text-xs text-text-muted">{{ $user['created_at'] ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-text-muted">No active users found.</td></tr>
                    @endforelse
                </tbody></table></div>
            </section>
        @endif
    </main>
</body>
</html>
