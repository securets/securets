<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Security Notifications – Secure ThreatShield</title>
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

            <!-- Notifications (Active) -->
            <a href="{{ url('/admin/notifications') }}" class="nav-item active" title="Security Notifications">
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
        <header class="admin-header">
            <div class="header-left">
                <h1 class="page-title">Security Notifications</h1>
                <span class="shield-badge-tag">Incident Alert Log</span>
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

                <!-- User Dropdown -->
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
                        <a href="{{ url('/admin/settings') }}" class="dropdown-item">Settings</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <!-- Header with Filter Tabs & Actions -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div class="notifications-tabs" style="margin-bottom: 0; border-bottom: none;">
                    <button type="button" class="notif-tab-btn active" data-filter="all">All Alerts (5)</button>
                    <button type="button" class="notif-tab-btn" data-filter="lost-device">Lost Devices (2)</button>
                    <button type="button" class="notif-tab-btn" data-filter="recovery">Recovery Tickets (2)</button>
                    <button type="button" class="notif-tab-btn" data-filter="security">Security Events (1)</button>
                </div>

                <button type="button" class="btn-secondary-action" id="btn-mark-all-read">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Mark All as Read
                </button>
            </div>

            <!-- Notifications Stack -->
            <div class="notifications-stack">
                @foreach($notifications as $notif)
                    <div class="notification-item {{ $notif['unread'] ? 'unread' : '' }} {{ $notif['urgent'] ? 'urgent' : '' }}" 
                         data-category="{{ $notif['category'] }}">
                        <div class="notif-icon-box" style="background: {{ $notif['urgent'] ? 'rgba(239, 68, 68, 0.15)' : 'rgba(37, 99, 235, 0.15)' }}; color: {{ $notif['urgent'] ? '#f87171' : '#60a5fa' }};">
                            @if($notif['icon'] === 'laptop')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                    <line x1="8" y1="21" x2="16" y2="21"/>
                                </svg>
                            @elseif($notif['icon'] === 'phone')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            @elseif($notif['icon'] === 'key')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="8" cy="15" r="5"/>
                                    <path d="M11.5 11.5L20 3"/>
                                    <path d="M16 7l2 2"/>
                                </svg>
                            @elseif($notif['icon'] === 'shield')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            @else
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            @endif
                        </div>

                        <div class="notif-body">
                            <div class="notif-title-row">
                                <h3 class="notif-headline">{{ $notif['title'] }}</h3>
                                <span class="notif-time">{{ $notif['time'] }}</span>
                            </div>
                            <p class="notif-message">{{ $notif['message'] }}</p>
                            
                            <div style="margin-top: 10px; display: flex; gap: 8px;">
                                <a href="{{ url('/admin') }}" class="btn-primary-action" style="padding: 4px 12px; font-size: 11px; text-decoration: none;">
                                    Take Action on Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <script src="{{ asset('admin-assets/admin.js') }}"></script>
</body>
</html>

