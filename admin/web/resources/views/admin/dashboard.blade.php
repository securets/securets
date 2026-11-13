@php
    $adminName = $adminName ?? 'Admin';
    $pendingCount = $pendingCount ?? 0;
    $revokedDevicesCount = $revokedDevicesCount ?? 0;
    $activeBypassCodes = $activeBypassCodes ?? 0;
    $totalProtected = $totalProtected ?? 0;
    $recoveryRequests = $recoveryRequests ?? [];
    $activeDevices = $activeDevices ?? [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard – Account Recovery & Device Kill Switch</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Admin Dedicated CSS -->
    <link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}">
</head>
<body>

    <!-- ── Left Sidebar Navigation Rail ── -->
    <aside class="admin-sidebar" aria-label="Admin Navigation">
        <!-- Brand Logo / Squircle Shield -->
        <a href="{{ url('/admin') }}" class="sidebar-logo" title="Secure ThreatShield Admin">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <circle cx="12" cy="10" r="2" fill="currentColor"/>
                <path d="M12 12v3"/>
            </svg>
        </a>

        <!-- Sidebar Navigation Icons -->
        <nav class="sidebar-nav">
            <!-- Home / Dashboard (Active) -->
            <a href="{{ url('/admin') }}" class="nav-item active" title="Account Recovery & Devices">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </a>

            <!-- Notifications Center -->
            <a href="{{ url('/admin/notifications') }}" class="nav-item" title="Security Notifications">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="nav-badge-count">2</span>
            </a>

            <!-- Settings -->
            <a href="{{ url('/admin/settings') }}" class="nav-item" title="Recovery & Security Settings">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </a>
        </nav>
    </aside>

    <!-- ── Main Administrative Area ── -->
    <main class="admin-main">
        <!-- Top Navigation Bar -->
        <header class="admin-header">
            <div class="header-left">
                <h1 class="page-title">Account Recovery &amp; Device Kill Switch</h1>
                <span class="shield-badge-tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                    </svg>
                    Emergency Assistance Center
                </span>
            </div>

            <div class="header-right">
                <!-- ThreatShield Brand Mark -->
                <div class="brand-mark">
                    <div class="brand-icon-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <circle cx="12" cy="10" r="1.5" fill="currentColor"/>
                            <path d="M12 11.5v2.5"/>
                        </svg>
                    </div>
                    <div class="brand-shield-secondary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                        </svg>
                    </div>
                    <div class="brand-text-block">
                        <span class="brand-text-title">Secure</span>
                        <span class="brand-text-sub">ThreatShield</span>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div style="position: relative;">
                    <button type="button" class="account-trigger" id="account-trigger" aria-label="Account Settings">
                        <div class="user-avatar-circle">
                            <span>{{ strtoupper(substr($adminName, 0, 1)) }}</span>
                        </div>
                        <span class="account-label">{{ $adminName }}</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    <div class="dropdown-menu" id="account-dropdown">
                        <a href="{{ url('/admin/notifications') }}" class="dropdown-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            </svg>
                            Notification Center
                        </a>
                        <a href="{{ url('/admin/settings') }}" class="dropdown-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Recovery Settings
                        </a>
                        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 4px 0;">
                        <a href="{{ url('/dashboard') }}" class="dropdown-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            </svg>
                            Exit to User Portal
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ── Main Content ── -->
        <div class="admin-content">

            <!-- ── Top Metric Stats ── -->
            <div class="stats-overview">
                <!-- Stat 1: Pending Recovery Requests -->
                <div class="stat-box amber">
                    <div class="stat-header">
                        <span class="stat-title">Pending Lockouts</span>
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number">{{ $pendingCount }}</div>
                    <div class="stat-desc">Users unable to sign in (lost phone/laptop)</div>
                </div>

                <!-- Stat 2: Devices Locked Down / Revoked -->
                <div class="stat-box red">
                    <div class="stat-header">
                        <span class="stat-title">Revoked / Wiped Devices</span>
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number">{{ $revokedDevicesCount }}</div>
                    <div class="stat-desc">Remote kill switch executed</div>
                </div>

                <!-- Stat 3: Active Emergency Bypass Codes -->
                <div class="stat-box blue">
                    <div class="stat-header">
                        <span class="stat-title">Active Bypass Codes</span>
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 2l-2 2m-2-2l2 2"/>
                                <circle cx="8" cy="15" r="5"/>
                                <path d="M11.5 11.5L20 3"/>
                                <path d="M16 7l2 2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number">{{ $activeBypassCodes }}</div>
                    <div class="stat-desc">Single-use emergency codes generated</div>
                </div>

                <!-- Stat 4: Protected Users -->
                <div class="stat-box green">
                    <div class="stat-header">
                        <span class="stat-title">Protected Accounts</span>
                        <div class="stat-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number">{{ $totalProtected }}</div>
                    <div class="stat-desc">Enforced 2FA &amp; ThreatShield security</div>
                </div>
            </div>

            <!-- ── Main 2-Column Grid Layout ── -->
            <div class="features-grid-layout">

                <!-- ── Column 1: Account Recovery Requests (Lost Phone / Laptop) ── -->
                <section class="admin-card-panel">
                    <div class="panel-header">
                        <div class="panel-title-wrap">
                            <div class="panel-icon-wrap" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="panel-title">Account Recovery &amp; Lockout Tickets</h2>
                                <p class="panel-subtitle">Assistance for users who lost their phone or laptop and cannot log back in</p>
                            </div>
                        </div>

                        <!-- Manual Emergency Dispatch -->
                        <button type="button" class="btn-primary-action btn-open-code-modal" 
                                data-user-email="manual.entry@threatshield.internal" 
                                data-user-name="Manual Recovery Issue" 
                                data-device-name="Lost Device / Offline Phone">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                            Issue Emergency Bypass Code
                        </button>
                    </div>

                    <!-- Recovery Cards List -->
                    <div class="recovery-requests-list">
                        @foreach($recoveryRequests as $req)
                            <article class="recovery-card">
                                <div class="recovery-card-top">
                                    <div class="user-identity-block">
                                        <div class="user-avatar-tag">
                                            {{ strtoupper(substr($req['user_name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="user-name-title">{{ $req['user_name'] }}</div>
                                            <div class="user-email-sub">{{ $req['user_email'] }}</div>
                                        </div>
                                    </div>

                                    <span class="badge-pill {{ $req['incident_badge'] }}">
                                        {{ $req['incident_type'] }}
                                    </span>
                                </div>

                                <!-- Incident Metadata Box -->
                                <div class="recovery-incident-info">
                                    <div>
                                        <div class="incident-prop-label">Lost / Compromised Device</div>
                                        <div class="incident-prop-val">{{ $req['device_name'] }}</div>
                                    </div>
                                    <div>
                                        <div class="incident-prop-label">Reported At</div>
                                        <div class="incident-prop-val">{{ $req['reported_at'] }}</div>
                                    </div>
                                    <div>
                                        <div class="incident-prop-label">Last Known IP Address</div>
                                        <div class="incident-prop-val">{{ $req['last_ip'] }}</div>
                                    </div>
                                    <div>
                                        <div class="incident-prop-label">Lockout Status</div>
                                        <div class="incident-prop-val" style="color: #fbbf24;">{{ $req['status'] }}</div>
                                    </div>
                                </div>

                                <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                                    {{ $req['description'] }}
                                </p>

                                <!-- Action Buttons -->
                                <div class="recovery-actions-row">
                                    <!-- Generate Code Button -->
                                    <button type="button" class="btn-primary-action btn-open-code-modal"
                                            data-user-email="{{ $req['user_email'] }}"
                                            data-user-name="{{ $req['user_name'] }}"
                                            data-device-name="{{ $req['device_name'] }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                        Generate Bypass Code
                                    </button>

                                    <!-- Emergency Remote Wipe / Kill Switch -->
                                    <button type="button" class="btn-killswitch btn-trigger-killswitch"
                                            data-device-name="{{ $req['device_name'] }}"
                                            data-user-email="{{ $req['user_email'] }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                        </svg>
                                        Remote Wipe Lost Device
                                    </button>

                                    <!-- Approve Recovery -->
                                    <button type="button" class="btn-secondary-action btn-approve-recovery"
                                            data-user-email="{{ $req['user_email'] }}"
                                            style="color: #4ade80; border-color: rgba(34, 197, 94, 0.3);">
                                        Approve
                                    </button>

                                    <!-- Decline -->
                                    <button type="button" class="btn-secondary-action btn-reject-recovery"
                                            data-user-email="{{ $req['user_email'] }}">
                                        Decline
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <!-- ── Column 2: Emergency Device Kill Switch & Session Lockdown ── -->
                <section class="admin-card-panel">
                    <div class="panel-header">
                        <div class="panel-title-wrap">
                            <div class="panel-icon-wrap" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                    <line x1="8" y1="21" x2="16" y2="21"/>
                                    <line x1="12" y1="17" x2="12" y2="21"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="panel-title">Device Kill Switch &amp; Sessions</h2>
                                <p class="panel-subtitle">Remotely revoke active sessions on lost or compromised hardware</p>
                            </div>
                        </div>
                    </div>

                    <div class="devices-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User &amp; Device</th>
                                    <th>Status</th>
                                    <th>Kill Switch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeDevices as $dev)
                                    <tr>
                                        <td>
                                            <div class="device-cell">
                                                <div class="device-type-icon">
                                                    @if($dev['device_type'] === 'Mobile')
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                                            <line x1="12" y1="18" x2="12.01" y2="18"/>
                                                        </svg>
                                                    @else
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                                            <line x1="8" y1="21" x2="16" y2="21"/>
                                                            <line x1="12" y1="17" x2="12" y2="21"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; font-size: 13px;">{{ $dev['device_name'] }}</div>
                                                    <div style="font-size: 11px; color: var(--text-dim);">{{ $dev['user_name'] }} • {{ $dev['os'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-pill {{ $dev['status_class'] }} status-col">
                                                {{ $dev['status'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($dev['status'] !== 'Revoked')
                                                <button type="button" class="btn-killswitch btn-trigger-killswitch"
                                                        data-device-name="{{ $dev['device_name'] }}"
                                                        data-user-email="{{ $dev['user_email'] }}"
                                                        style="padding: 4px 10px; font-size: 11px;">
                                                    Remote Wipe
                                                </button>
                                            @else
                                                <span style="font-size: 11px; color: var(--text-dim);">Decommissioned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick Security Tip -->
                    <div style="margin-top: 20px; background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; padding: 14px; display: flex; gap: 12px; align-items: flex-start;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div style="font-size: 12px; color: #94a3b8; line-height: 1.4;">
                            <strong style="color: #ffffff;">ThreatShield Protocol:</strong> Triggering a remote wipe immediately invalidates all active Sanctum tokens, terminates Expo push bindings, and locks the local database on the target device.
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- ── Emergency Bypass Code Generator Modal ── -->
    <div class="modal-overlay" id="recovery-code-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Emergency Recovery Code</h3>
                <button type="button" class="btn-secondary-action" id="modal-close-btn" style="padding: 4px 8px;">&times;</button>
            </div>

            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">
                Provide this single-use emergency bypass code to the verified user. This allows them to bypass the lost 2FA phone/laptop and sign in from a new secure browser.
            </p>

            <div style="background: rgba(15, 23, 42, 0.6); border-radius: 10px; padding: 12px 14px; margin-top: 14px; font-size: 12px;">
                <div style="color: var(--text-dim); margin-bottom: 2px;">Target User:</div>
                <div style="font-weight: 600; color: #ffffff;" id="modal-target-user">-</div>
                <div style="color: var(--text-dim); margin-top: 8px; margin-bottom: 2px;">Reported Incident:</div>
                <div style="color: #fbbf24;" id="modal-target-device">Lost Phone / Authenticator</div>
            </div>

            <!-- Generated Code Display -->
            <div class="code-display-box">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); margin-bottom: 6px;">Single-Use Bypass Code (Expires in 60m)</div>
                <div class="bypass-code-text" id="generated-bypass-code">STS-8492-X9</div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-secondary-action" onclick="document.getElementById('recovery-code-modal').classList.remove('open')">Done</button>
                <button type="button" class="btn-primary-action" id="btn-copy-code">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy Code
                </button>
            </div>
        </div>
    </div>

    <!-- Admin Dedicated JavaScript -->
    <script src="{{ asset('admin-assets/admin.js') }}"></script>
</body>
</html>
