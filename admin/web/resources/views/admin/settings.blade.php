<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Recovery &amp; Security Settings – Secure ThreatShield</title>
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
        <a href="{{ url('/admin') }}" class="sidebar-logo" title="Secure ThreatShield Admin">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <circle cx="12" cy="10" r="2" fill="currentColor"/>
                <path d="M12 12v3"/>
            </svg>
        </a>

        <nav class="sidebar-nav">
            <!-- Home / Dashboard -->
            <a href="{{ url('/admin') }}" class="nav-item" title="Account Recovery & Devices">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </a>

            <!-- Notifications -->
            <a href="{{ url('/admin/notifications') }}" class="nav-item" title="Security Notifications">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="nav-badge-count">2</span>
            </a>

            <!-- Settings (Active) -->
            <a href="{{ url('/admin/settings') }}" class="nav-item active" title="Recovery & Security Settings">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </a>
        </nav>
    </aside>

    <!-- ── Main Administrative Area ── -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <h1 class="page-title">Recovery &amp; Security Settings</h1>
                <span class="shield-badge-tag">Lockout Policy Rules</span>
            </div>

            <div class="header-right">
                <div class="brand-mark">
                    <div class="brand-icon-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <div class="brand-text-block">
                        <span class="brand-text-title">Secure</span>
                        <span class="brand-text-sub">ThreatShield</span>
                    </div>
                </div>

                <div style="position: relative;">
                    <button type="button" class="account-trigger" id="account-trigger">
                        <div class="user-avatar-circle">
                            <span>{{ strtoupper(substr($adminName, 0, 1)) }}</span>
                        </div>
                        <span class="account-label">{{ $adminName }}</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="account-dropdown">
                        <a href="{{ url('/admin') }}" class="dropdown-item">Admin Dashboard</a>
                        <a href="{{ url('/admin/notifications') }}" class="dropdown-item">Notifications</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content" style="max-width: 900px;">
            <form id="admin-settings-form">
                
                <!-- Section 1: Account Recovery Policies -->
                <div class="settings-section-card">
                    <div class="panel-header" style="margin-bottom: 12px;">
                        <div class="panel-title-wrap">
                            <div class="panel-icon-wrap" style="background: rgba(37, 99, 235, 0.15); color: #60a5fa;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="panel-title">Lost Phone / Laptop Recovery Policies</h2>
                                <p class="panel-subtitle">Controls how users regain account access when hardware 2FA is unavailable</p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Code Expiry -->
                    <div class="settings-row">
                        <div>
                            <div class="settings-label-title">Emergency Bypass Code Validity</div>
                            <div class="settings-label-desc">Time window before a generated recovery code expires if not redeemed by the locked-out user.</div>
                        </div>
                        <select style="background: #0d1527; border: 1px solid var(--border-color); color: #ffffff; padding: 8px 14px; border-radius: 8px; font-size: 13px; outline: none;">
                            <option value="15">15 Minutes</option>
                            <option value="30">30 Minutes</option>
                            <option value="60" selected>60 Minutes (Recommended)</option>
                            <option value="1440">24 Hours</option>
                        </select>
                    </div>

                    <!-- Row 2: Secondary Verification Requirement -->
                    <div class="settings-row">
                        <div>
                            <div class="settings-label-title">Require Secondary Identity Verification</div>
                            <div class="settings-label-desc">Requires manager sign-off or government ID verification before an emergency bypass code can be issued.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Row 3: Auto 2FA Reset -->
                    <div class="settings-row">
                        <div>
                            <div class="settings-label-title">Auto-Prompt Replacement 2FA Re-enrollment</div>
                            <div class="settings-label-desc">Forces the user to immediately pair a new smartphone authenticator upon logging in with the emergency code.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Section 2: Lost / Compromised Device Lockdown -->
                <div class="settings-section-card">
                    <div class="panel-header" style="margin-bottom: 12px;">
                        <div class="panel-title-wrap">
                            <div class="panel-icon-wrap" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="panel-title">Device Remote Wipe &amp; Lockdown Protocol</h2>
                                <p class="panel-subtitle">Automated actions triggered when a phone or laptop is reported lost or stolen</p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Instant Session Termination -->
                    <div class="settings-row">
                        <div>
                            <div class="settings-label-title">Instant Token &amp; Push Revocation</div>
                            <div class="settings-label-desc">Immediately invalidates Laravel Sanctum session tokens and unbinds Expo mobile push tokens when a device is flagged lost.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Row 2: Auto-Blacklist -->
                    <div class="settings-row">
                        <div>
                            <div class="settings-label-title">Hardware Fingerprint Blacklisting</div>
                            <div class="settings-label-desc">Permanently prevents the reported lost phone or laptop hardware ID from communicating with ThreatShield APIs.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Section 3: Emergency Dispatch Channels -->
                <div class="settings-section-card">
                    <div class="panel-header" style="margin-bottom: 12px;">
                        <div class="panel-title-wrap">
                            <div class="panel-icon-wrap" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="panel-title">Emergency Alert Channels</h2>
                                <p class="panel-subtitle">Dispatch destinations for urgent lockout tickets and stolen device reports</p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Primary Admin Security Email</label>
                        <input type="email" value="security-ops@threatshield.internal" 
                               style="width: 100%; padding: 10px 14px; background: #0c1426; border: 1px solid var(--border-color); border-radius: 8px; color: #ffffff; font-size: 14px; outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Urgent SMS / Webhook Endpoint (Optional)</label>
                        <input type="url" placeholder="https://api.threatshield.internal/webhooks/emergency-lockout" 
                               style="width: 100%; padding: 10px 14px; background: #0c1426; border: 1px solid var(--border-color); border-radius: 8px; color: #ffffff; font-size: 14px; outline: none;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="submit" class="btn-primary-action" style="padding: 10px 24px; font-size: 14px;">
                        Save Recovery Policies
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="{{ asset('admin-assets/admin.js') }}"></script>
</body>
</html>

