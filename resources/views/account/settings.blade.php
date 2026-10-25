<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings – SecureThreatShield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; }
        :root {
            --blue: #3D5AF1;
            --blue-hover: #2E46DB;
            --dark: #0F172A;
            --mid: #1E293B;
            --card-bg: #162032;
            --border: rgba(255,255,255,.08);
            --text: #F8FAFC;
            --muted: #94A3B8;
            --success-bg: rgba(34,197,94,.12);
            --success-border: rgba(34,197,94,.3);
            --success-text: #86EFAC;
            --danger-bg: rgba(239,68,68,.12);
            --danger-border: rgba(239,68,68,.3);
            --danger-text: #FCA5A5;
        }
        body { margin:0; min-height:100vh; background:var(--dark); color:var(--text); font-family:'Inter',sans-serif; }
        @include('account.navigation-styles')

        main { width:min(720px, calc(100% - 40px)); margin:0 auto; padding:48px 0 64px; }
        h1 { margin:0 0 8px; font-size:28px; font-weight:800; }
        .intro { margin:0 0 28px; color:var(--muted); font-size:14px; }

        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .alert-success { background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success-text); }
        .alert-danger  { background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger-text); }

        .cards-stack { display: grid; gap: 24px; }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .card-header { margin-bottom: 22px; }
        .card-header h2 { margin:0 0 6px; font-size:17px; font-weight:700; }
        .card-header p { margin:0; color:var(--muted); font-size:13px; line-height:1.5; }

        /* Security Status Block */
        .security-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-top: 14px; }
        .security-item {
            background: #0B1120;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .security-item-title { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        .security-item-value { font-size: 16px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active { background: rgba(34,197,94,0.15); color: #4ADE80; border: 1px solid rgba(34,197,94,0.3); }

        /* Toggle Rows */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }
        .toggle-row:first-child { padding-top: 0; }
        .toggle-row:last-child { border-bottom: 0; padding-bottom: 0; }
        .toggle-info h3 { margin: 0 0 4px; font-size: 15px; font-weight: 600; }
        .toggle-info p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.4; }

        /* Switch UI */
        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 26px;
            flex-shrink: 0;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: rgba(255,255,255,0.14);
            transition: .2s;
            border-radius: 26px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .2s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: var(--blue); }
        input:focus + .slider { box-shadow: 0 0 0 3px rgba(61,90,241,0.3); }
        input:checked + .slider:before { transform: translateX(20px); }

        /* Theme Selector */
        .theme-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 8px; }
        .theme-card {
            position: relative;
            background: #0B1120;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            transition: border-color .2s, color .2s, background .2s;
        }
        .theme-card input { position: absolute; opacity: 0; }
        .theme-card:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }
        .theme-card.is-selected {
            border-color: var(--blue);
            color: #fff;
            background: rgba(61,90,241,0.08);
        }

        .btn-primary {
            background: var(--blue);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 11px 24px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }
        .btn-primary:hover { background: var(--blue-hover); }
        .btn-primary:active { transform: scale(0.98); }

        @media (max-width:640px) {
            main { width:min(100% - 32px, 720px); padding:32px 0; }
            .card { padding:20px; }
            .theme-options { grid-template-columns: 1fr; }
            .btn-primary { width: 100%; }
        }
    </style>
</head>
<body>
    @include('account.navigation', ['activeNav' => 'settings'])
    @include('account.profile-menu')

    <main>
        <h1>Settings</h1>
        <p class="intro">Review account preferences, security telemetry, and display configurations.</p>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <span>Please check the form for errors.</span>
            </div>
        @endif

        <div class="cards-stack">
            <!-- Account Security Telemetry Card -->
            <section class="card">
                <div class="card-header">
                    <h2>Account Security & Protection</h2>
                    <p>Live status of your authentication guards and linked devices.</p>
                </div>
                <div class="security-grid">
                    <div class="security-item">
                        <span class="security-item-title">Protection Status</span>
                        <div class="security-item-value">
                            <span class="status-badge status-active">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="security-item">
                        <span class="security-item-title">Paired Mobile Devices</span>
                        <div class="security-item-value">
                            {{ $deviceCount }} {{ Str::plural('device', $deviceCount) }}
                        </div>
                    </div>
                    <div class="security-item">
                        <span class="security-item-title">Active Integrations</span>
                        <div class="security-item-value">
                            <a href="{{ route('integrations') }}" style="color:#818CF8;text-decoration:none">
                                {{ $integrationCount }} {{ Str::plural('app', $integrationCount) }} &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Preferences Form -->
            <form method="POST" action="{{ route('settings.update') }}" class="cards-stack">
                @csrf
                @method('PATCH')

                <!-- Notifications Card -->
                <section class="card">
                    <div class="card-header">
                        <h2>Notification Preferences</h2>
                        <p>Configure which security alerts and MFA approval prompts you receive.</p>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-info">
                            <h3>Email Security Alerts</h3>
                            <p>Send an immediate email alert when an unrecognized login attempt or gate access occurs.</p>
                        </div>
                        <label class="switch" aria-label="Toggle email security alerts">
                            <input type="checkbox" name="email_notifications" value="1"
                                   {{ old('email_notifications', $preferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-info">
                            <h3>Mobile Push Notifications</h3>
                            <p>Send instant biometric / one-tap approval prompts to your registered ThreatShield mobile devices.</p>
                        </div>
                        <label class="switch" aria-label="Toggle mobile push notifications">
                            <input type="checkbox" name="mobile_push" value="1"
                                   {{ old('mobile_push', $preferences['mobile_push'] ?? true) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </section>

                <!-- Appearance Card -->
                <section class="card">
                    <div class="card-header">
                        <h2>Appearance & Theme</h2>
                        <p>Customize the visual interface and display modes for your dashboard.</p>
                    </div>

                    @php
                        $selectedTheme = old('theme', $preferences['theme'] ?? 'dark');
                    @endphp

                    <div class="theme-options">
                        <label class="theme-card {{ $selectedTheme === 'dark' ? 'is-selected' : '' }}">
                            <input type="radio" name="theme" value="dark" {{ $selectedTheme === 'dark' ? 'checked' : '' }}>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                            <span>Dark Mode</span>
                        </label>

                        <label class="theme-card {{ $selectedTheme === 'light' ? 'is-selected' : '' }}">
                            <input type="radio" name="theme" value="light" {{ $selectedTheme === 'light' ? 'checked' : '' }}>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <span>Light Mode</span>
                        </label>

                        <label class="theme-card {{ $selectedTheme === 'system' ? 'is-selected' : '' }}">
                            <input type="radio" name="theme" value="system" {{ $selectedTheme === 'system' ? 'checked' : '' }}>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                            <span>System Auto</span>
                        </label>
                    </div>
                </section>

                <div>
                    <button type="submit" class="btn-primary">Save Preferences</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        (() => {
            const themeCards = document.querySelectorAll('.theme-card');
            themeCards.forEach((card) => {
                const radio = card.querySelector('input[type="radio"]');
                radio.addEventListener('change', () => {
                    themeCards.forEach((c) => c.classList.remove('is-selected'));
                    if (radio.checked) {
                        card.classList.add('is-selected');
                    }
                });
            });
        })();
    </script>
</body>
</html>
