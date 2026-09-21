<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SecureTS – ThreatShield</title>
    <meta name="description" content="SecureTS ThreatShield – Multi-Factor Authentication and threat protection platform. Fast, secure, and passwordless.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-blue: #3866E6;
            --brand-dark: #0F172A;
            --brand-darker: #0A0F1E;
            --brand-mid: #1E293B;
            --brand-border: rgba(255,255,255,0.1);
            --text-primary: #F8FAFC;
            --text-muted: #94A3B8;
        }

        html, body { height: 100%; font-family: 'Inter', sans-serif; }

        /* ─── SPLASH SCREEN ─── */
        #splash {
            position: fixed; inset: 0;
            background: var(--brand-blue);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
            transition: opacity 0.7s ease, visibility 0.7s ease;
        }
        #splash.fade-out { opacity: 0; visibility: hidden; }

        .splash-logo {
            display: flex; flex-direction: column; align-items: center; gap: 12px;
            animation: splashPulse 2s ease-in-out infinite;
        }
        .splash-logo-row {
            display: flex; align-items: center; gap: 10px;
        }
        .splash-logo-icon {
            display: flex; align-items: center; gap: 6px;
        }
        .hexagon {
            width: 40px; height: 40px;
            background: #0F172A;
            clip-path: polygon(50% 0%,93% 25%,93% 75%,50% 100%,7% 75%,7% 25%);
            display: flex; align-items: center; justify-content: center;
        }
        .hexagon svg { color: white; }
        .shield-icon { width: 36px; height: 40px; }
        .brand-text-wrap { display: flex; flex-direction: column; }
        .brand-name { font-size: 22px; font-weight: 700; color: #0F172A; letter-spacing: -0.3px; line-height: 1; }
        .brand-sub  { font-size: 11px; font-weight: 500; color: #0F172A; letter-spacing: 0.5px; }

        @keyframes splashPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.04); opacity: 0.92; }
        }

        /* ─── MAIN SITE ─── */
        #main-site {
            opacity: 0;
            transition: opacity 0.7s ease;
            min-height: 100vh;
            background: var(--brand-dark);
            color: var(--text-primary);
        }
        #main-site.visible { opacity: 1; }

        /* ─── NAVBAR ─── */
        nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 64px;
            background: var(--brand-darker);
            border-bottom: 1px solid var(--brand-border);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-logo { display: flex; align-items: center; gap: 8px; }
        .nav-logo .hexagon { width: 30px; height: 30px; }
        .nav-logo .brand-name { font-size: 16px; color: white; }
        .nav-logo .brand-sub  { font-size: 9px; color: var(--text-muted); }
        .nav-links { display: flex; align-items: center; gap: 28px; }
        .nav-links a {
            color: var(--text-muted); font-size: 14px; font-weight: 500;
            text-decoration: none; transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--text-primary); }
        .nav-right { display: flex; align-items: center; gap: 12px; }
        .search-box {
            display: flex; align-items: center; gap: 8px;
            background: var(--brand-mid); border: 1px solid var(--brand-border);
            border-radius: 8px; padding: 7px 14px;
            color: var(--text-muted); font-size: 13px; font-family: 'Inter', sans-serif;
        }
        .search-box input {
            background: transparent; border: none; outline: none;
            color: var(--text-muted); font-size: 13px; width: 120px; font-family: 'Inter', sans-serif;
        }
        .search-box input::placeholder { color: var(--text-muted); }
        .btn-signin {
            background: var(--brand-blue); color: white;
            border: none; border-radius: 8px;
            padding: 9px 20px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: background 0.2s, transform 0.1s;
            font-family: 'Inter', sans-serif;
        }
        .btn-signin:hover { background: #2c55d0; transform: translateY(-1px); }

        /* ─── HERO SECTION ─── */
        .hero {
            display: flex; flex-direction: column; align-items: center;
            text-align: center; padding: 100px 24px 80px;
        }
        .hero h1 {
            font-size: clamp(32px, 5vw, 52px);
            font-weight: 800; line-height: 1.15;
            color: var(--text-primary);
            max-width: 620px; margin-bottom: 20px;
        }
        .hero p {
            font-size: 16px; color: var(--text-muted);
            max-width: 480px; line-height: 1.7; margin-bottom: 40px;
        }
        .hero-buttons { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; justify-content: center; }
        .btn-discover {
            display: flex; align-items: center; gap: 8px;
            background: var(--text-primary); color: var(--brand-dark);
            border: none; border-radius: 10px; padding: 14px 26px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .btn-discover:hover { background: #E2E8F0; transform: translateY(-2px); }
        .btn-try {
            background: var(--brand-blue); color: white;
            border: none; border-radius: 10px; padding: 14px 26px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .btn-try:hover { background: #2c55d0; transform: translateY(-2px); }

        /* ─── STATS BAR ─── */
        .stats-bar {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; padding: 24px;
            flex-wrap: wrap;
        }
        .stat-card {
            background: var(--brand-mid); border: 1px solid var(--brand-border);
            border-radius: 10px; padding: 14px 22px; min-width: 130px; text-align: center;
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
        }
        .stat-card.yellow::before { background: #EAB308; }
        .stat-card.magenta::before { background: #E11D48; }
        .stat-card.green::before  { background: #22C55E; }
        .stat-card.blue::before   { background: var(--brand-blue); }

        .stat-value {
            font-size: 15px; font-weight: 700; margin-bottom: 4px;
        }
        .stat-card.yellow  .stat-value  { color: #EAB308; }
        .stat-card.magenta .stat-value  { color: #E11D48; }
        .stat-card.green   .stat-value  { color: #22C55E; }
        .stat-card.blue    .stat-value  { color: var(--brand-blue); }

        .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════ SPLASH SCREEN ═══════════════════════════════ --}}
    <div id="splash">
        <div class="splash-logo">
            <div class="splash-logo-row">
                <div class="splash-logo-icon">
                    {{-- Hexagon lock --}}
                    <div class="hexagon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3.1-9H8.9V6a3.1 3.1 0 0 1 6.2 0v2z"/>
                        </svg>
                    </div>
                    {{-- Shield --}}
                    <svg class="shield-icon" viewBox="0 0 36 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 0L0 7v13c0 11 7.5 20.9 18 24 10.5-3.1 18-13 18-24V7L18 0z" fill="#1E293B"/>
                        <path d="M18 0L0 7v13h18V0z" fill="#C0392B"/>
                        <path d="M18 7L0 7v13h18V7z" fill="#E74C3C" opacity="0.3"/>
                        <path d="M18 0L36 7v13H18V0z" fill="#ECF0F1"/>
                        <path d="M18 20H0v13c0 0 7.5 7 18 7V20z" fill="#27AE60"/>
                        <path d="M18 20H36v13c0 0-7.5 7-18 7V20z" fill="#2980B9"/>
                        <path d="M14 14l-4 4 6 6 10-10-2-2-8 8-4-4-2 2 6 6 10-10-2-2z" fill="white" opacity="0.8"/>
                    </svg>
                </div>
                <div class="brand-text-wrap">
                    <span class="brand-name">Secure<span style="color:#1E3A8A">TS</span></span>
                    <span class="brand-sub">ThreatShield</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════ MAIN SITE ═══════════════════════════════ --}}
    <div id="main-site">

        {{-- NAVBAR --}}
        <nav>
            <div class="nav-logo">
                <div class="hexagon" style="width:30px;height:30px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="white">
                        <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3.1-9H8.9V6a3.1 3.1 0 0 1 6.2 0v2z"/>
                    </svg>
                </div>
                <svg width="26" height="30" viewBox="0 0 36 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 0L0 7v13c0 11 7.5 20.9 18 24 10.5-3.1 18-13 18-24V7L18 0z" fill="#1E293B"/>
                    <path d="M18 0L0 7v13h18V0z" fill="#C0392B"/>
                    <path d="M18 0L36 7v13H18V0z" fill="#ECF0F1"/>
                    <path d="M18 20H0v13c0 0 7.5 7 18 7V20z" fill="#27AE60"/>
                    <path d="M18 20H36v13c0 0-7.5 7-18 7V20z" fill="#2980B9"/>
                </svg>
                <div class="brand-text-wrap">
                    <span class="brand-name" style="font-size:15px;color:white;">Secure<span style="color:var(--brand-blue)">TS</span></span>
                    <span class="brand-sub" style="color:var(--text-muted);font-size:8px;">ThreatShield</span>
                </div>
            </div>

            <div class="nav-links">
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#app">App</a>
                <a href="#faq">FAQ</a>
            </div>

            <div class="nav-right">
                <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" placeholder="Search keyword...">
                </div>
                <button class="btn-signin" id="signInBtn">SIGN IN</button>
            </div>
        </nav>

        {{-- HERO --}}
        <section class="hero" id="about">
            <h1>Really Big Inspiration Text about this Website</h1>
            <p>A Short Description About the Really Big Inspiration Text about this Website above</p>
            <div class="hero-buttons">
                <button class="btn-discover" id="discoverBtn">
                    Discover
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 5v14M5 12l7 7 7-7"/>
                    </svg>
                </button>
                <button class="btn-try" id="tryBtn">Try it now!</button>
            </div>
        </section>

        {{-- STATS BAR --}}
        <div class="stats-bar" id="features">
            <div class="stat-card yellow">
                <div class="stat-value">&lt;500ms</div>
                <div class="stat-label">Auth Latency</div>
            </div>
            <div class="stat-card magenta">
                <div class="stat-value">0 Passwords</div>
                <div class="stat-label">DB Credentials</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value">100%</div>
                <div class="stat-label">No Payment Method</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value">Protected</div>
                <div class="stat-label">Phishing immune</div>
            </div>
        </div>

    </div>

    <script>
        // 5-second splash then reveal main site
        const splash    = document.getElementById('splash');
        const mainSite  = document.getElementById('main-site');

        setTimeout(function () {
            splash.classList.add('fade-out');
            mainSite.classList.add('visible');
        }, 5000);
    </script>

</body>
</html>
