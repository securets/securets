@php
    $navItems = [
        'dashboard' => ['⌂', 'Dashboard'],
        'users' => ['♙', 'User Management'],
        'purchases' => ['▤', 'Purchase History'],
        'sessions' => ['◫', 'Active Sessions'],
        'recovery' => ['⌁', 'Lost Device Recovery'],
        'audit' => ['✓', 'Security Audit'],
        'status' => ['ϟ', 'System Status'],
    ];
    $themeLabels = ['dark' => 'Dark', 'light' => 'Light', 'system' => 'Match my device'];
    $accentColors = ['blue' => '#3B66F5', 'violet' => '#7C5CF5', 'emerald' => '#0E9F6E', 'amber' => '#D97706', 'rose' => '#E11D48'];
    $densityLabels = ['comfortable' => 'Comfortable', 'compact' => 'Compact'];
@endphp
<!DOCTYPE html>
<html lang="en" class="dark"
      data-theme="{{ $preferences['theme'] }}"
      data-accent="{{ $preferences['accent'] }}"
      data-density="{{ $preferences['density'] }}"
      data-start-page="{{ $preferences['start_page'] }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Admin — SecureTS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="security-console bg-canvas text-text font-sans antialiased min-h-screen">
    <div class="flex min-h-screen">
        <aside class="security-sidebar w-64 lg:w-[250px] bg-sidebar border-r border-border flex flex-col shrink-0">
            <div class="p-5 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="shield-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3Z"/><path d="m9.5 12 1.7 1.7 3.5-3.5"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold tracking-[0.18em] text-white">SECURITY ADMIN</div>
                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-emerald"><span class="status-dot"></span> <span id="sidebar-health">Checking…</span></div>
                    </div>
                </div>
            </div>

            <nav class="p-4 flex flex-col gap-1" aria-label="Security Admin Navigation">
                @foreach ($navItems as $tab => [$icon, $label])
                    <button type="button" data-tab="{{ $tab }}" @class(['admin-nav-item', 'is-active' => $tab === 'dashboard', 'admin-nav-alert' => $tab === 'recovery'])>
                        <span class="nav-icon">{{ $icon }}</span><span>{{ $label }}</span>
                        @if ($tab === 'sessions')<span id="sessions-count" class="nav-badge">—</span>@endif
                        @if ($tab === 'recovery')<span id="recovery-count" class="nav-badge is-amber">—</span>@endif
                        @if ($tab === 'status')<span class="status-dot ml-auto"></span>@endif
                    </button>
                @endforeach
                <div class="nav-divider" role="separator"></div>
                <button type="button" data-tab="personalize" class="admin-nav-item"><span class="nav-icon">◐</span><span>Theme &amp; Personalization</span></button>
            </nav>

            <div class="mt-auto p-5 border-t border-border">
                <div class="text-xs text-white font-medium">{{ auth()->user()?->name ?? 'Admin' }}</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ auth()->user()?->email }}</div>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-[11px] text-slate-400 hover:text-white">Sign out</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 min-w-0 p-5 lg:p-8">
            <header class="flex flex-col xl:flex-row xl:items-center justify-between gap-5 mb-7">
                <div>
                    <p class="eyebrow">SECURITY OPERATIONS CENTER</p>
                    <h1 id="page-title" class="text-2xl lg:text-3xl font-semibold tracking-tight mt-2">Security Overview</h1>
                    <p id="page-description" class="text-sm text-slate-400 mt-2">Monitor users, sessions, recovery requests, and platform health in real time.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="search-box">
                        <span class="text-slate-500">⌕</span>
                        <input id="global-search" type="search" placeholder="Search rows on this page..." aria-label="Search the rows shown on this page">
                        <kbd>Ctrl K</kbd>
                    </label>
                </div>
            </header>

            {{-- ── Dashboard ── --}}
            <div id="view-dashboard" data-view class="admin-view is-visible">
                <div class="section-stack">
                    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" aria-label="Security KPIs">
                        <button type="button" data-tab="users" class="kpi-card text-left"><span class="kpi-label">Total / Active Users</span><strong id="metric-users">—</strong><span class="kpi-meta text-emerald">From registered accounts <span>↗</span></span></button>
                        <button type="button" data-tab="sessions" class="kpi-card text-left"><span class="kpi-label">Active Sessions</span><strong id="metric-sessions">—</strong><span class="kpi-meta text-sky-400"><span class="status-dot bg-sky-400"></span> Last 24 hours <span>↗</span></span></button>
                        <button type="button" data-tab="recovery" class="kpi-card kpi-alert text-left"><span class="kpi-label">Pending Lost Devices</span><strong id="metric-devices" class="text-amber">—</strong><span class="kpi-meta text-amber">Requires immediate action <span>↗</span></span></button>
                        <button type="button" data-tab="status" class="kpi-card text-left"><span class="kpi-label">System Health</span><strong id="metric-health">—</strong><span class="kpi-meta text-emerald">Live platform health <span>↗</span></span></button>
                    </section>

                    <section class="panel" aria-labelledby="sales-title">
                        <div class="panel-header">
                            <div><h2 id="sales-title">Subscription Sales</h2><p>Active subscribers and estimated monthly revenue for each subscription tier</p></div>
                            <span class="badge badge-blue">Est. monthly revenue: <span id="sales-mrr">—</span></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead><tr><th>Tier</th><th>Price</th><th class="num">Active Subscribers</th><th>Share of Users</th><th class="num">On Trial</th><th class="num">Est. Monthly Revenue</th></tr></thead>
                                <tbody id="sales-tiers"><tr><td colspan="6" class="text-slate-500">Loading subscription sales…</td></tr></tbody>
                                <tfoot id="sales-total"></tfoot>
                            </table>
                        </div>

                        <div class="panel-header mt-7"><div><h3>Monthly Sales</h3><p>Paid subscriptions active during each month, new subscriptions, and estimated revenue</p></div></div>
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead id="sales-monthly-head"><tr><th>Month</th></tr></thead>
                                <tbody id="sales-monthly"><tr><td class="text-slate-500">Loading monthly sales…</td></tr></tbody>
                            </table>
                        </div>
                        <p id="sales-note" class="panel-note"></p>
                    </section>

                    <section class="panel">
                        <div class="panel-header"><div><h2>Active Lost Device Queue</h2><p>Reported devices requiring security action</p></div><button type="button" data-tab="recovery" class="text-xs text-primary hover:text-white">View queue →</button></div>
                        <div class="server-table" data-server-table="devices" data-renderer="devices">
                            <div data-table-toolbar></div>
                            <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Device / Owner</th><th>Last Known IP / Location</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody data-table-body><tr><td colspan="4" class="text-slate-500">Loading devices…</td></tr></tbody></table></div>
                            <div data-table-pager></div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header"><div><h2>Recent Security Audit</h2><p>Latest authentication and administrative events</p></div><button type="button" data-tab="audit" class="text-xs text-primary hover:text-white">View Full Audit Log →</button></div>
                        <div class="server-table" data-server-table="audit" data-renderer="audit">
                            <div data-table-toolbar></div>
                            <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Timestamp</th><th>Actor</th><th>Action</th><th>Target Resource</th><th>Severity / Status</th></tr></thead><tbody data-table-body><tr><td colspan="5" class="text-slate-500">Loading audit events…</td></tr></tbody></table></div>
                            <div data-table-pager></div>
                        </div>
                    </section>
                </div>
            </div>

            {{-- ── User Management ── --}}
            <div id="view-users" data-view class="admin-view">
                <section class="panel">
                    <div class="panel-header"><div><h2>User Management</h2><p>Registered accounts, subscription tiers, and connected applications</p></div></div>
                    <div class="server-table" data-server-table="users" data-renderer="users">
                        <div data-table-toolbar></div>
                        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Account</th><th>Subscription</th><th>Connected Apps</th><th>Paired Devices</th><th>Joined</th><th class="text-right">Purchases</th></tr></thead><tbody data-table-body><tr><td colspan="6" class="text-slate-500">Loading users…</td></tr></tbody></table></div>
                        <div data-table-pager></div>
                    </div>
                </section>
            </div>

            {{-- ── Purchase History ── --}}
            <div id="view-purchases" data-view class="admin-view">
                <section class="panel">
                    <div class="panel-header"><div><h2>Purchase History</h2><p>Every subscription a user has bought: the month, tier, and when it expires</p></div></div>
                    <div class="server-table" id="purchases-table" data-server-table="purchases" data-renderer="purchases">
                        <div data-table-toolbar></div>
                        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>User</th><th>Month</th><th>Tier</th><th>Price</th><th>Status</th><th>Started</th><th>Expiration</th><th><span class="sr-only">Receipt</span></th></tr></thead><tbody data-table-body><tr><td colspan="8" class="text-slate-500">Loading purchases…</td></tr></tbody></table></div>
                        <div data-table-pager></div>
                    </div>
                </section>
            </div>

            {{-- ── Active Sessions ── --}}
            <div id="view-sessions" data-view class="admin-view">
                <section class="panel">
                    <div class="panel-header"><div><h2>Active Sessions</h2><p>Authenticated web sessions and paired mobile instances</p></div><span id="sessions-summary" class="badge badge-blue">— active</span></div>
                    <div class="server-table" data-server-table="sessions" data-renderer="sessions">
                        <div data-table-toolbar></div>
                        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>User</th><th>OS / Device</th><th>Browser / Client</th><th>IP Address</th><th>Last Seen</th></tr></thead><tbody data-table-body><tr><td colspan="5" class="text-slate-500">Loading sessions…</td></tr></tbody></table></div>
                        <div data-table-pager></div>
                    </div>
                </section>
            </div>

            {{-- ── Lost Device Recovery ── --}}
            <div id="view-recovery" data-view class="admin-view">
                <section class="panel">
                    <div class="panel-header"><div><h2>Lost Device Recovery</h2><p>Pending instance token revocations</p></div><span id="recovery-summary" class="badge badge-amber">— pending</span></div>
                    <div class="server-table" data-server-table="devices" data-renderer="recovery">
                        <div data-table-toolbar></div>
                        <div class="recovery-grid" data-table-body><div class="recovery-card"><span class="text-slate-500">Loading recovery devices…</span></div></div>
                        <div data-table-pager></div>
                    </div>
                </section>
            </div>

            {{-- ── Security Audit ── --}}
            <div id="view-audit" data-view class="admin-view">
                <section class="panel">
                    <div class="panel-header"><div><h2>Security Audit Trail</h2><p>Live authentication and verification stream</p></div><span class="live-label"><span class="status-dot"></span> Live</span></div>
                    <div class="server-table" data-server-table="audit" data-renderer="audit">
                        <div data-table-toolbar></div>
                        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Timestamp</th><th>Actor</th><th>Action</th><th>Target Resource</th><th>Severity / Status</th></tr></thead><tbody data-table-body><tr><td colspan="5" class="text-slate-500">Loading audit events…</td></tr></tbody></table></div>
                        <div data-table-pager></div>
                    </div>
                </section>
            </div>

            {{-- ── System Status ── --}}
            <div id="view-status" data-view class="admin-view">
                <section class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="panel status-card"><span>Admin API</span><strong id="status-api">—</strong><small id="status-api-meta">Checking summary endpoint</small></div>
                    <div class="panel status-card"><span>Database Health</span><strong id="status-db">—</strong><small id="status-db-meta">Checking Supabase connection</small></div>
                </section>
            </div>

            {{-- ── Theme & Personalization ── --}}
            <div id="view-personalize" data-view class="admin-view">
                <section class="panel">
                    <div class="panel-header">
                        <div><h2>Theme &amp; Personalization</h2><p>These settings change how the console looks for your admin account only. Changes preview right away.</p></div>
                        <span id="prefs-status" class="badge badge-slate" role="status">Saved</span>
                    </div>
                    <form id="preferences-form" class="prefs-form">
                        <fieldset class="prefs-group">
                            <legend>Theme</legend>
                            <div class="choice-row">
                                @foreach ($preferenceOptions['theme'] as $value)
                                    <label class="choice"><input type="radio" name="theme" value="{{ $value }}" @checked($preferences['theme'] === $value)><span>{{ $themeLabels[$value] ?? ucfirst($value) }}</span></label>
                                @endforeach
                            </div>
                            <p class="prefs-hint">"Match my device" follows your computer's light or dark setting.</p>
                        </fieldset>

                        <fieldset class="prefs-group">
                            <legend>Accent color</legend>
                            <div class="choice-row">
                                @foreach ($preferenceOptions['accent'] as $value)
                                    <label class="choice"><input type="radio" name="accent" value="{{ $value }}" @checked($preferences['accent'] === $value)><span><i class="swatch" style="background: {{ $accentColors[$value] ?? '#3B66F5' }}"></i>{{ ucfirst($value) }}</span></label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="prefs-group">
                            <legend>Density</legend>
                            <div class="choice-row">
                                @foreach ($preferenceOptions['density'] as $value)
                                    <label class="choice"><input type="radio" name="density" value="{{ $value }}" @checked($preferences['density'] === $value)><span>{{ $densityLabels[$value] ?? ucfirst($value) }}</span></label>
                                @endforeach
                            </div>
                            <p class="prefs-hint">Compact fits more table rows on the screen.</p>
                        </fieldset>

                        <fieldset class="prefs-group">
                            <legend>Start page</legend>
                            <select name="start_page" class="table-select" aria-label="Page to open after signing in">
                                @foreach ($preferenceOptions['start_page'] as $value)
                                    <option value="{{ $value }}" @selected($preferences['start_page'] === $value)>{{ $navItems[$value][1] ?? ucfirst($value) }}</option>
                                @endforeach
                            </select>
                            <p class="prefs-hint">The page the console opens on after you sign in.</p>
                        </fieldset>

                        <div class="prefs-actions">
                            <button type="submit" class="primary-button">Save preferences</button>
                            <button type="button" id="prefs-reset" class="ghost-button">Reset to default</button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
