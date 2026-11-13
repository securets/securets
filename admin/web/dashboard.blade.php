<!DOCTYPE html>
<html lang="en" class="dark">
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
                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-emerald"><span class="status-dot"></span> System Operational</div>
                    </div>
                </div>
            </div>

            <nav class="p-4 flex flex-col gap-1" aria-label="Security Admin Navigation">
                <button type="button" data-tab="dashboard" class="admin-nav-item is-active"><span class="nav-icon">⌂</span><span>Dashboard</span></button>
                <button type="button" data-tab="users" class="admin-nav-item"><span class="nav-icon">♙</span><span>User Management</span></button>
                <button type="button" data-tab="sessions" class="admin-nav-item"><span class="nav-icon">◫</span><span>Active Sessions</span><span class="nav-badge">128</span></button>
                <button type="button" data-tab="recovery" class="admin-nav-item admin-nav-alert"><span class="nav-icon">⌁</span><span>Lost Device Recovery</span><span class="nav-badge is-amber">3</span></button>
                <button type="button" data-tab="audit" class="admin-nav-item"><span class="nav-icon">✓</span><span>Security Audit</span></button>
                <button type="button" data-tab="status" class="admin-nav-item"><span class="nav-icon">ϟ</span><span>System Status</span><span class="status-dot ml-auto"></span></button>
            </nav>

            <div class="mt-auto p-5 border-t border-border">
                <div class="text-xs text-white font-medium">Admin</div>
                <div class="text-[11px] text-slate-400 mt-1">Authenticated admin session</div>
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
                        <input id="global-search" type="search" placeholder="Search User ID, IP, or token..." aria-label="Search security records">
                        <kbd>Ctrl K</kbd>
                    </label>
                    <button type="button" id="simulate-event" class="primary-button whitespace-nowrap">＋ Simulate Security Event</button>
                </div>
            </header>

            <div id="view-dashboard" data-view class="admin-view is-visible">
                <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6" aria-label="Security KPIs">
                    <button type="button" data-tab="users" class="kpi-card text-left"><span class="kpi-label">Total / Active Users</span><strong id="metric-users">—</strong><span class="kpi-meta text-emerald">From registered accounts <span>↗</span></span></button>
                    <button type="button" data-tab="sessions" class="kpi-card text-left"><span class="kpi-label">Active Sessions</span><strong id="metric-sessions">—</strong><span class="kpi-meta text-sky-400"><span class="status-dot bg-sky-400"></span> Last 24 hours <span>↗</span></span></button>
                    <button type="button" data-tab="recovery" class="kpi-card kpi-alert text-left"><span class="kpi-label">Pending Lost Devices</span><strong id="metric-devices" class="text-amber">—</strong><span class="kpi-meta text-amber">Requires immediate action <span>↗</span></span></button>
                    <button type="button" data-tab="status" class="kpi-card text-left"><span class="kpi-label">System Health</span><strong id="metric-health">—</strong><span class="kpi-meta text-emerald">Live platform health <span>↗</span></span></button>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-5 gap-5 mb-6">
                    <div class="panel xl:col-span-3">
                        <div class="panel-header"><div><h2>Active Lost Device Queue</h2><p>Reported devices requiring security action</p></div><button type="button" data-tab="recovery" class="text-xs text-primary hover:text-white">View queue →</button></div>
                        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Device / Owner</th><th>Last Known IP / Location</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody id="device-queue">
                            <tr data-device-row><td><div class="font-medium text-white">iPhone 16 Pro</div><div class="text-xs text-slate-500">elena.rostova@acme.dev</div></td><td><div class="font-mono text-xs">185.220.101.44</div><div class="text-xs text-slate-500">Manila, PH · 4m ago</div></td><td><span class="badge badge-amber device-status">Reported Lost</span></td><td><div class="action-group"><button type="button" data-device-action="Remote Lock">Lock</button><button type="button" data-device-action="Revoke Tokens">Revoke</button></div></td></tr>
                            <tr data-device-row><td><div class="font-medium text-white">Pixel 9 Pro</div><div class="text-xs text-slate-500">m.chen@finvault.io</div></td><td><div class="font-mono text-xs">203.0.113.89</div><div class="text-xs text-slate-500">Singapore · 19m ago</div></td><td><span class="badge badge-crimson device-status">Needs Review</span></td><td><div class="action-group"><button type="button" data-device-action="Remote Lock">Lock</button><button type="button" data-device-action="Revoke Tokens">Revoke</button></div></td></tr>
                            <tr data-device-row><td><div class="font-medium text-white">iPhone 15</div><div class="text-xs text-slate-500">d.okafor@nimbus.co</div></td><td><div class="font-mono text-xs">198.51.100.17</div><div class="text-xs text-slate-500">Lagos, NG · 42m ago</div></td><td><span class="badge badge-amber device-status">Reported Lost</span></td><td><div class="action-group"><button type="button" data-device-action="Remote Lock">Lock</button><button type="button" data-device-action="Revoke Tokens">Revoke</button></div></td></tr>
                        </tbody></table></div>
                    </div>
                    <div class="panel xl:col-span-2"><div class="panel-header"><div><h2>Authentication Telemetry</h2><p>Successful vs failed attempts · 24 hours</p></div><span class="live-label"><span class="status-dot"></span> Live</span></div><div class="chart-legend"><span><i class="legend-success"></i>Successful logins</span><span><i class="legend-failed"></i>Failed attempts</span></div><svg class="telemetry-chart" viewBox="0 0 520 220" role="img" aria-label="Authentication telemetry chart"><defs><linearGradient id="chart-gradient" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#3B66F5"/><stop offset="1" stop-color="#3B66F5" stop-opacity="0"/></linearGradient></defs><path class="chart-grid" d="M35 30H510M35 75H510M35 120H510M35 165H510M35 210H510"/><path class="chart-area" d="M35 170 C75 145 86 155 120 130 S170 150 205 108 S255 115 290 92 S340 126 375 72 S425 93 460 57 S490 65 510 42 V210 H35Z"/><path class="chart-line-success" d="M35 170 C75 145 86 155 120 130 S170 150 205 108 S255 115 290 92 S340 126 375 72 S425 93 460 57 S490 65 510 42"/><path class="chart-line-failed" d="M35 192 C75 185 90 194 120 180 S170 190 205 170 S255 181 290 158 S340 174 375 145 S425 162 460 132 S490 144 510 120"/><g class="chart-labels"><text x="35" y="218">00:00</text><text x="150" y="218">06:00</text><text x="270" y="218">12:00</text><text x="390" y="218">18:00</text><text x="480" y="218">Now</text></g></svg></div>
                </section>

                        <section class="panel"><div class="panel-header"><div><h2>Recent Security Audit</h2><p>Latest authentication and administrative events</p></div><button type="button" data-tab="audit" class="text-xs text-primary hover:text-white">View Full Audit Log →</button></div><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Timestamp</th><th>Actor</th><th>Action</th><th>Target Resource</th><th>Severity / Status</th></tr></thead><tbody id="audit-snapshot"></tbody></table></div></section>
            </div>

            <div id="view-users" data-view class="admin-view"><section class="panel"><div class="panel-header"><div><h2>User Management</h2><p>Registered accounts, subscription tiers, and connected applications</p></div><input class="table-filter" data-filter="users" type="search" placeholder="Filter by email..."></div><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Account</th><th>Subscription</th><th>Connected Apps</th><th>Paired Devices</th><th>Action</th></tr></thead><tbody><tr><td>elena.rostova@acme.dev</td><td><span class="badge badge-blue">Enterprise</span></td><td>14</td><td>3 devices</td><td><button class="table-action">Suspend Account</button></td></tr><tr><td>s.lindqvist@polar.sh</td><td><span class="badge badge-blue">Scale</span></td><td>6</td><td>2 devices</td><td><button class="table-action">Suspend Account</button></td></tr><tr><td>r.vance@citadel.org</td><td><span class="badge badge-blue">Scale</span></td><td>8</td><td>1 device</td><td><button class="table-action">Suspend Account</button></td></tr><tr><td>k.takahashi@kuro.jp</td><td><span class="badge badge-slate">Starter</span></td><td>2</td><td>1 device</td><td><button class="table-action">Suspend Account</button></td></tr></tbody></table></div></section></div>
            <div id="view-sessions" data-view class="admin-view"><section class="panel"><div class="panel-header"><div><h2>Active Sessions</h2><p>Authenticated web sessions and paired mobile instances</p></div><span class="badge badge-blue">128 active</span></div><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>User</th><th>OS / Device</th><th>Browser / Client</th><th>IP Address</th><th>Actions</th></tr></thead><tbody><tr><td>s.lindqvist@polar.sh</td><td>macOS 15.1</td><td>Safari 18.1</td><td class="font-mono text-xs">185.220.101.44</td><td><button class="table-action">Force Logout</button> <button class="table-action">Revoke Device</button></td></tr><tr><td>k.takahashi@kuro.jp</td><td>Windows 11</td><td>Firefox 132.0</td><td class="font-mono text-xs">203.0.113.89</td><td><button class="table-action">Force Logout</button> <button class="table-action">Revoke Device</button></td></tr><tr><td>a.verma@lattice.io</td><td>iOS 18.1</td><td>SecureTS Mobile 2.4</td><td class="font-mono text-xs">198.51.100.17</td><td><button class="table-action">Force Logout</button> <button class="table-action">Revoke Device</button></td></tr></tbody></table></div></section></div>
            <div id="view-recovery" data-view class="admin-view"><section class="panel"><div class="panel-header"><div><h2>Lost Device Recovery</h2><p>Pending instance token revocations</p></div><span class="badge badge-amber">3 pending</span></div><div class="recovery-grid"><div class="recovery-card"><strong>iPhone 16 Pro</strong><span>elena.rostova@acme.dev</span><span class="font-mono">inst_9f8a...31c</span><button class="primary-button">Approve &amp; Revoke</button></div><div class="recovery-card"><strong>Pixel 9 Pro</strong><span>m.chen@finvault.io</span><span class="font-mono">inst_4b2d...89e</span><button class="primary-button">Approve &amp; Revoke</button></div><div class="recovery-card"><strong>iPhone 15</strong><span>d.okafor@nimbus.co</span><span class="font-mono">inst_7c1e...04a</span><button class="primary-button">Approve &amp; Revoke</button></div></div></section></div>
            <div id="view-audit" data-view class="admin-view"><section class="panel"><div class="panel-header"><div><h2>Security Audit Trail</h2><p>Live authentication and verification stream</p></div><span class="live-label"><span class="status-dot"></span> Live</span></div><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Timestamp</th><th>Actor</th><th>Action</th><th>Target Resource</th><th>Severity / Status</th></tr></thead><tbody id="audit-full"><tr><td>10:42:18 UTC</td><td>Security Engine</td><td>Multiple failed TOTP attempts</td><td>r.vance@citadel.org</td><td><span class="badge badge-crimson">Action Required</span></td></tr><tr><td>10:39:04 UTC</td><td>Owner</td><td>Push Approval Denied</td><td>req_01jc8...9k</td><td><span class="badge badge-slate">Logged</span></td></tr><tr><td>10:21:50 UTC</td><td>s.lindqvist</td><td>Target App Secret Rotated</td><td>int_prod_21f</td><td><span class="badge badge-blue">Completed</span></td></tr></tbody></table></div></section></div>
            <div id="view-status" data-view class="admin-view">
                <section class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                    <div class="panel status-card"><span>Authentication API</span><strong class="text-emerald">Operational</strong><small>Admin API responding</small></div>
                    <div class="panel status-card"><span>Database Health</span><strong>Operational</strong><small>Configured admin database</small></div>
                    <div class="panel status-card"><span>Realtime Transport</span><strong>Not configured</strong><small>Polling dashboard data</small></div>
                </section>
                <section id="emergency-lockdown-panel" class="panel emergency-panel" aria-labelledby="emergency-lockdown-title">
                    <div class="panel-header">
                        <div><h2 id="emergency-lockdown-title">Emergency Lockdown</h2><p>Freeze the admin application during a suspected breach. This revokes active sessions and access tokens.</p></div>
                        <span id="lockdown-status" class="badge badge-slate">Inactive</span>
                    </div>
                    <div class="emergency-actions">
                        <div><strong id="lockdown-message">System operating normally.</strong><small id="lockdown-details">Lockdown is reversible after the incident is contained.</small></div>
                        <button type="button" id="emergency-lockdown" class="danger-button">Activate Lockdown</button>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
