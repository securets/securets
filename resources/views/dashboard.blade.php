<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard – SecureThreatShield</title>
    <meta name="description" content="SecureThreatShield Dashboard – threat protection overview.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-blue: #3D5AF1;
            --brand-dark: #0F172A;
            --brand-mid:  #1E293B;
            --brand-border: rgba(255,255,255,0.08);
            --text-primary: #F8FAFC;
            --text-muted: #94A3B8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--brand-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        @include('account.navigation-styles')

        /* ── Content ── */
        .content { padding: 48px 40px; max-width: 1100px; margin: 0 auto; }

        .page-header { margin-bottom: 36px; }
        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .page-header p { font-size: 14px; color: var(--text-muted); }

        /* ── Stat cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }
        .stat-card {
            background: var(--brand-mid);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
        }
        .stat-card.blue::before   { background: var(--brand-blue); }
        .stat-card.green::before  { background: #22C55E; }
        .stat-card.yellow::before { background: #EAB308; }
        .stat-card.red::before    { background: #EF4444; }
        .stat-label {
            font-size: 12px; font-weight: 500;
            color: var(--text-muted); margin-bottom: 10px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .stat-value { font-size: 26px; font-weight: 800; }
        .stat-card.blue   .stat-value { color: #818CF8; }
        .stat-card.green  .stat-value { color: #4ADE80; }
        .stat-card.yellow .stat-value { color: #FACC15; }
        .stat-card.red    .stat-value { color: #F87171; }

        /* ── Status panel ── */
        .status-panel {
            background: var(--brand-mid);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            padding: 28px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .status-icon {
            width: 56px; height: 56px;
            background: rgba(34,197,94,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .status-text h2 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .status-text p  { font-size: 14px; color: var(--text-muted); }
    </style>
</head>
<body>

<!-- ── Navbar ── -->
@include('account.navigation', ['activeNav' => 'dashboard'])
@include('account.profile-menu')

<!-- ── Main Content ── -->
<div class="content">
    <div class="page-header">
        <h1>Welcome to your dashboard! 👋</h1>
        <p>Here's your SecureThreatShield protection overview.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Protection Status</div>
            <div class="stat-value">Active</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Threats Blocked</div>
            <div class="stat-value">0</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-label">Auth Latency</div>
            <div class="stat-value">&lt;500ms</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Alerts</div>
            <div class="stat-value">0</div>
        </div>
    </div>

    <div class="status-panel">
        <div class="status-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>
        <div class="status-text">
            <h2 style="color:#4ADE80">All Systems Operational</h2>
            <p>ThreatShield is actively monitoring your account. No threats detected.</p>
        </div>
    </div>

    <a href="{{ route('integrations') }}" class="status-panel" style="text-decoration:none;color:inherit;margin-top:16px;display:flex">
        <div class="status-icon" style="background:rgba(61,90,241,0.12)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#818CF8" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
        </div>
        <div class="status-text">
            <h2>Integration Hub</h2>
            <p>Connect an external app’s login page, get a secret key, and manage apps for this account. Approve or decline logins on the mobile app.</p>
        </div>
    </a>
</div>

</body>
</html>
