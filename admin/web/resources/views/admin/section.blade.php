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
        <div class="p-6 border-t border-border text-sm text-text-muted">Root Administrator</div>
    </aside>

    <main class="flex-1 p-8 flex flex-col gap-8 overflow-y-auto">
        <header class="flex items-center justify-between border-b border-border pb-6">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-text">{{ $pageTitle }}</h1>
                <p class="text-sm text-text-muted mt-1">{{ $pageDescription }}</p>
            </div>
            <span class="text-xs font-mono tabular-nums text-text-muted px-3 py-2 rounded-lg bg-surface border border-border">Reverb WS: Connected</span>
        </header>

        @if ($section === 'status')
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6" aria-label="Global System Status">
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col justify-between gap-4">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-text-muted">Access Request API Latency (p95)</span><span class="text-xs font-medium text-accent">● Operational</span></div>
                    <div class="flex items-baseline justify-between"><span class="text-2xl font-semibold font-mono text-text">142 ms</span><span class="text-xs text-text-muted">Target: &lt; 2000 ms</span></div>
                </div>
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col justify-between gap-4">
                    <span class="text-sm font-medium text-text-muted">Database Health (Supabase Primary)</span>
                    <div class="flex items-baseline justify-between"><span class="text-2xl font-semibold font-mono text-text">99.98%</span><span class="text-xs font-mono text-text-muted">Pool: 18 / 100 active</span></div>
                </div>
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col justify-between gap-4">
                    <div><h2 class="text-sm font-semibold text-text">Emergency Lockdown</h2><p class="text-xs text-text-muted mt-1">Pause all new Target App authentication requests globally.</p></div>
                    <div class="flex items-center justify-between pt-2 border-t border-border"><span class="text-xs text-text-muted">Current Mode</span><span class="text-xs font-mono text-text">Standard Enforcement</span></div>
                </div>
            </section>
        @elseif ($section === 'recovery')
            <section class="grid grid-cols-1 xl:grid-cols-3 gap-6" aria-label="Lost Device Recovery">
                <div class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6 xl:col-span-3">
                    <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">Lost Device Recovery</h2><p class="text-xs text-text-muted mt-0.5">Pending instance token revocations</p></div><span class="text-xs font-mono font-semibold text-accent px-2.5 py-1 rounded-lg border border-border">3 Pending</span></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ([['elena.rostova@acme.dev', 'iPhone 16 Pro', 'inst_9f8a...31c', '4m ago'], ['m.chen@FinVault.io', 'Pixel 9 Pro', 'inst_4b2d...89e', '19m ago'], ['d.okafor@nimbus.co', 'iPhone 15', 'inst_7c1e...04a', '42m ago']] as [$email, $device, $token, $time])
                            <div class="p-4 rounded-lg bg-background border border-border flex flex-col gap-3"><div class="flex items-start justify-between"><div><p class="text-sm font-medium text-text">{{ $email }}</p><p class="text-xs text-text-muted mt-0.5">{{ $device }} · Token <span class="font-mono">{{ $token }}</span></p></div><span class="text-xs font-mono text-text-muted">{{ $time }}</span></div><div class="flex items-center gap-2 pt-2 border-t border-border"><button type="button" class="flex-1 min-h-11 px-3 py-2 rounded-lg bg-primary text-text text-xs font-medium">Approve &amp; Revoke</button><button type="button" class="min-h-11 px-3 py-2 rounded-lg border border-border text-text-muted text-xs font-medium">Dismiss</button></div></div>
                        @endforeach
                    </div>
                </div>
            </section>
        @elseif ($section === 'sessions')
            <section class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6" aria-label="Active Sessions">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">Active Sessions</h2><p class="text-xs text-text-muted mt-0.5">Authenticated web sessions and paired mobile instances</p></div><span class="text-xs text-text-muted font-mono">Showing 4 of 128 active</span></div>
                <div class="overflow-x-auto"><table class="w-full text-left border-collapse"><thead><tr class="border-b border-border text-xs font-medium text-text-muted"><th class="pb-3 pr-4">User</th><th class="pb-3 px-4">OS / Device</th><th class="pb-3 px-4">Browser / Client</th><th class="pb-3 px-4">IP Address</th><th class="pb-3 pl-4 text-right">Actions</th></tr></thead><tbody class="divide-y divide-border text-sm">
                    @foreach ([['s.lindqvist@polar.sh', 'macOS 15.1', 'Safari 18.1', '185.220.101.44'], ['k.takahashi@kuro.jp', 'Windows 11', 'Firefox 132.0', '203.0.113.89'], ['a.verma@lattice.io', 'iOS 18.1 (iPhone)', 'SecureTS Mobile 2.4', '198.51.100.17'], ['j.miller@linear.app', 'Ubuntu 24.04 LTS', 'Chrome 130.0', '192.0.2.214']] as [$user, $device, $client, $ip])
                        <tr><td class="py-4 pr-4 font-medium text-text">{{ $user }}</td><td class="py-4 px-4 text-text-muted">{{ $device }}</td><td class="py-4 px-4 text-text-muted">{{ $client }}</td><td class="py-4 px-4 font-mono text-xs text-text-muted">{{ $ip }}</td><td class="py-4 pl-4 text-right"><button type="button" class="min-h-11 px-3 py-2 rounded-lg border border-border text-xs font-medium text-text">Force Logout</button> <button type="button" class="min-h-11 px-3 py-2 rounded-lg border border-border text-xs font-medium text-text-muted">Revoke Device</button></td></tr>
                    @endforeach
                </tbody></table></div>
            </section>
        @elseif ($section === 'audit')
            <section class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6" aria-label="Security Audit Trail">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">Security Audit Trail</h2><p class="text-xs text-text-muted mt-0.5">Live authentication and verification stream</p></div><span class="text-xs font-mono text-text-muted">Live</span></div>
                <div class="flex flex-col gap-4">
                    @foreach ([['Action Required', 'Multiple failed TOTP attempts', '6 consecutive RFC 6238 verification failures for r.vance@citadel.org via Target App int_prod_88a (IP 91.132.14.88).'], ['Push Approval Denied', 'Owner explicitly denied access request req_01jc8...9k from 45.155.205.11.', '10:39:04 UTC'], ['Target App Secret Rotated', 'API secret hashed and rotated for integration int_prod_21f by s.lindqvist@polar.sh.', '10:21:50 UTC'], ['Access Request Expired', '60s TTL elapsed for req_01jc8...4m; automatically resolved as denied.', '10:14:12 UTC']] as [$title, $message, $time])
                        <div class="p-4 rounded-lg bg-background border {{ $title === 'Action Required' ? 'border-accent' : 'border-border' }} flex flex-col gap-2"><div class="flex items-center justify-between"><span class="text-xs font-semibold text-text">{{ $title }}</span><span class="text-xs font-mono text-text-muted">{{ $time }}</span></div><p class="text-sm {{ $title === 'Action Required' ? 'font-semibold' : '' }} text-text">{{ $title === 'Action Required' ? $message : '' }}</p><p class="text-xs text-text-muted">{{ $title === 'Action Required' ? $time : $message }}</p></div>
                    @endforeach
                </div>
            </section>
        @elseif ($section === 'users')
            <section class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6" aria-label="User Management">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-text">User Management</h2><p class="text-xs text-text-muted mt-0.5">Registered accounts, subscription tiers, and Target App integrations</p></div><input type="search" placeholder="Filter by email..." aria-label="Filter users by email" class="min-h-11 px-3 py-2 rounded-lg bg-background border border-border text-sm text-text"></div>
                <div class="overflow-x-auto"><table class="w-full text-left border-collapse"><thead><tr class="border-b border-border text-xs font-medium text-text-muted"><th class="pb-3 pr-4">Account</th><th class="pb-3 px-4">Subscription Tier</th><th class="pb-3 px-4">Connected Apps</th><th class="pb-3 px-4">Paired Devices</th><th class="pb-3 pl-4 text-right">Account Action</th></tr></thead><tbody class="divide-y divide-border text-sm">
                    @foreach ([['elena.rostova@acme.dev', 'Enterprise', '14', '3 devices'], ['s.lindqvist@polar.sh', 'Scale', '6', '2 devices'], ['r.vance@citadel.org', 'Scale', '8', '1 device'], ['k.takahashi@kuro.jp', 'Starter', '2', '1 device']] as [$email, $tier, $apps, $devices])
                        <tr><td class="py-4 pr-4"><div class="font-medium text-text">{{ $email }}</div><div class="text-xs text-text-muted font-mono">usr_01h9x...72k</div></td><td class="py-4 px-4"><span class="px-2.5 py-1 rounded-lg border border-border text-xs font-medium text-text">{{ $tier }}</span></td><td class="py-4 px-4 font-mono text-text">{{ $apps }}</td><td class="py-4 px-4 font-mono text-text-muted">{{ $devices }}</td><td class="py-4 pl-4 text-right"><button type="button" class="min-h-11 px-3 py-2 rounded-lg border border-border text-xs font-medium text-text">Suspend Account</button></td></tr>
                    @endforeach
                </tbody></table></div>
            </section>
        @endif
    </main>
</body>
</html>
