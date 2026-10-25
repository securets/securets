<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Integration Hub – SecureThreatShield</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            --danger: #EF4444;
            --success: #22C55E;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--brand-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        @include('account.navigation-styles')

        /* ── Page layout ── */
        .page { max-width: 1100px; margin: 0 auto; padding: 40px 32px; }

        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 32px;
        }
        .page-header h1 { font-size: 24px; font-weight: 800; }
        .page-header p  { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

        /* ── Tabs ── */
        .tabs { display: flex; gap: 4px; margin-bottom: 28px; }
        .tab-btn {
            padding: 8px 20px; border-radius: 8px; border: none; cursor: pointer;
            font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;
            background: transparent; color: var(--text-muted);
            transition: background 0.2s, color 0.2s;
        }
        .tab-btn.active { background: rgba(61,90,241,0.18); color: #818CF8; }
        .tab-btn:hover:not(.active) { background: var(--brand-mid); color: var(--text-primary); }

        /* ── Add App Button ── */
        .btn-add {
            display: flex; align-items: center; gap: 8px;
            background: var(--brand-blue); color: #fff; border: none;
            border-radius: 10px; padding: 10px 20px; font-size: 14px;
            font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-add:hover { background: #2c55d0; transform: translateY(-1px); }

        /* ── App cards grid ── */
        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 16px;
        }
        .app-card {
            background: var(--brand-mid);
            border: 1px solid var(--brand-border);
            border-radius: 16px; padding: 20px;
            display: flex; flex-direction: column; gap: 14px;
            transition: border-color 0.2s, transform 0.15s;
        }
        .app-card:hover { border-color: rgba(61,90,241,0.4); transform: translateY(-2px); }
        .app-card-top { display: flex; align-items: center; gap: 12px; }
        .app-favicon {
            width: 44px; height: 44px; border-radius: 10px;
            background: rgba(255,255,255,0.06);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden;
        }
        .app-favicon img { width: 32px; height: 32px; object-fit: contain; }
        .app-info { flex: 1; min-width: 0; }
        .app-name { font-size: 15px; font-weight: 600; margin-bottom: 2px; }
        .app-url  { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Toggle switch */
        .toggle { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
        .toggle input { display: none; }
        .toggle-slider {
            position: absolute; inset: 0; border-radius: 12px;
            background: rgba(255,255,255,0.12); cursor: pointer;
            transition: background 0.25s;
        }
        .toggle-slider::before {
            content: ''; position: absolute;
            width: 18px; height: 18px; border-radius: 50%;
            background: #fff; top: 3px; left: 3px;
            transition: transform 0.25s;
        }
        .toggle input:checked + .toggle-slider { background: var(--brand-blue); }
        .toggle input:checked + .toggle-slider::before { transform: translateX(20px); }

        /* TOTP code */
        .totp-row {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(61,90,241,0.08); border: 1px solid rgba(61,90,241,0.2);
            border-radius: 10px; padding: 10px 14px;
        }
        .totp-label { font-size: 11px; font-weight: 500; color: #818CF8; margin-bottom: 2px; }
        .totp-code  { font-size: 22px; font-weight: 700; letter-spacing: 4px; color: #A5B4FC; font-family: monospace; }
        .totp-timer {
            font-size: 11px; color: var(--text-muted); text-align: right;
        }
        .totp-bar {
            height: 3px; border-radius: 2px;
            background: var(--brand-blue);
            margin-top: 4px; transition: width 1s linear;
        }

        /* Card actions */
        .app-card-actions { display: flex; gap: 8px; }
        .btn-sm {
            flex: 1; padding: 8px; border-radius: 8px; border: none;
            font-size: 12px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', sans-serif; transition: background 0.2s;
        }
        .btn-sm.danger { background: rgba(239,68,68,0.12); color: #F87171; }
        .btn-sm.danger:hover { background: rgba(239,68,68,0.22); }
        .btn-sm.info { background: rgba(61,90,241,0.12); color: #818CF8; }
        .btn-sm.info:hover { background: rgba(61,90,241,0.22); }

        /* ── Empty state ── */
        .empty-state {
            text-align: center; padding: 80px 24px;
            color: var(--text-muted);
        }
        .empty-state svg { margin-bottom: 20px; opacity: 0.4; }
        .empty-state h2 { font-size: 18px; font-weight: 600; margin-bottom: 8px; color: var(--text-primary); }
        .empty-state p  { font-size: 14px; }

        /* ── Login history / Devices table ── */
        .data-table {
            background: var(--brand-mid); border: 1px solid var(--brand-border);
            border-radius: 16px; overflow: hidden;
        }
        .data-table table { width: 100%; border-collapse: collapse; }
        .data-table th {
            text-align: left; font-size: 11px; font-weight: 600;
            color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;
            padding: 14px 20px; border-bottom: 1px solid var(--brand-border);
        }
        .data-table td {
            padding: 14px 20px; font-size: 13px;
            border-bottom: 1px solid var(--brand-border);
        }
        .data-table tr:last-child td { border-bottom: none; }
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .badge.approved { background: rgba(34,197,94,0.12); color: #4ADE80; }
        .badge.declined { background: rgba(239,68,68,0.12); color: #F87171; }
        .badge.pending  { background: rgba(234,179,8,0.12); color: #FACC15; }
        .badge.auto     { background: rgba(148,163,184,0.12); color: #94A3B8; }

        /* Pending approvals */
        .approval-card {
            background: var(--brand-mid); border: 1px solid rgba(234,179,8,0.3);
            border-radius: 14px; padding: 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            margin-bottom: 12px;
        }
        .approval-info { display: flex; align-items: center; gap: 12px; }
        .approval-favicon { width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; }
        .approval-favicon img { width: 28px; height: 28px; object-fit: contain; }
        .approval-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-approve {
            background: rgba(34,197,94,0.12); color: #4ADE80; border: 1px solid rgba(34,197,94,0.25);
            border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: background 0.2s;
        }
        .btn-approve:hover { background: rgba(34,197,94,0.22); }
        .btn-decline {
            background: rgba(239,68,68,0.12); color: #F87171; border: 1px solid rgba(239,68,68,0.25);
            border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: background 0.2s;
        }
        .btn-decline:hover { background: rgba(239,68,68,0.22); }

        /* ── Modal ── */
        .modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
            z-index: 200; align-items: center; justify-content: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: #1E293B; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 32px; width: 100%; max-width: 440px;
            animation: slideUp 0.2s ease;
        }
        @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .modal h2 { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .modal p  { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }
        .form-field { margin-bottom: 16px; }
        .form-field label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
        .form-field input {
            width: 100%; background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.1); border-radius: 8px;
            padding: 11px 14px; font-size: 14px; color: #fff;
            font-family: 'Inter', sans-serif; outline: none;
            transition: border-color 0.2s;
        }
        .form-field input:focus { border-color: var(--brand-blue); }
        .modal-actions { display: flex; gap: 10px; margin-top: 24px; }
        .btn-primary {
            flex: 1; background: var(--brand-blue); color: #fff; border: none;
            border-radius: 10px; padding: 12px; font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: background 0.2s;
        }
        .btn-primary:hover { background: #2c55d0; }
        .btn-cancel {
            flex: 1; background: rgba(255,255,255,0.06); color: var(--text-muted);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
            padding: 12px; font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: background 0.2s;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); }
        .btn-primary:disabled { opacity: 0.6; cursor: default; }

        .flash {
            margin-bottom: 16px; border-radius: 10px; padding: 12px 16px;
            font-size: 14px; font-weight: 500;
        }
        .flash.success { background: rgba(34,197,94,0.12); color: #4ADE80; }
        .flash.error { background: rgba(239,68,68,0.12); color: #F87171; }
        .flash.loading { background: rgba(61,90,241,0.12); color: #818CF8; }
        .modal-error { color: #F87171; font-size: 13px; margin-bottom: 12px; min-height: 1.2em; }

        /* Tabs panel */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
    </style>
</head>
<body>

<!-- ── Navbar ── -->
@include('account.navigation', ['activeNav' => 'integrations'])
@include('account.profile-menu')

<!-- ── Page Content ── -->
<div class="page">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 id="integration-hub">Integration Hub</h1>
            <p>Link any external app to this account. You get a login page link and a secret key. Approve or decline later logins on the mobile app.</p>
        </div>
        <button class="btn-add" onclick="openModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add App
        </button>
    </div>

    <div id="flash" class="flash" hidden></div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('apps', this)">My Apps</button>
        <button class="tab-btn" onclick="showTab('history', this)">Login History</button>
    </div>

    <!-- ── TAB: My Apps ── -->
    <div class="tab-panel active" id="tab-apps">
        @if($integrations->isEmpty())
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                <h2>No apps integrated yet</h2>
                <p>Click <strong>+ Add App</strong> to connect your first external application.</p>
            </div>
        @else
            <div class="apps-grid">
                @foreach($integrations as $app)
                <div class="app-card" id="card-{{ $app->id }}">
                    <div class="app-card-top">
                        <div class="app-favicon">
                            <img src="{{ $app->favicon }}" alt="{{ $app->name }}" onerror="this.src='/images/securets-logo.png'">
                        </div>
                        <div class="app-info">
                            <div class="app-name">{{ $app->name }}</div>
                            <div class="app-url">{{ $app->url }}</div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" {{ $app->is_active ? 'checked' : '' }}
                                   onchange="toggleApp({{ $app->id }}, this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <!-- TOTP Code -->
                    <div class="totp-row" id="totp-{{ $app->id }}">
                        <div>
                            <div class="totp-label">TOTP Code</div>
                            <div class="totp-code" id="code-{{ $app->id }}">──────</div>
                        </div>
                        <div class="totp-timer">
                            <div id="timer-{{ $app->id }}">30s</div>
                            <div class="totp-bar" id="bar-{{ $app->id }}" style="width:100%"></div>
                        </div>
                    </div>

                    <div class="app-card-actions">
                        <button class="btn-sm info" onclick="copyText('{{ $app->gateUrl() }}', 'Login page link copied!')">
                            Copy Login Link
                        </button>
                        <button class="btn-sm info" onclick="copyText('{{ $app->secret_key }}', 'Secret key copied!')">
                            Copy Secret
                        </button>
                        <button class="btn-sm danger" onclick="removeApp({{ $app->id }})">
                            Remove
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ── TAB: Login History ── -->
    <div class="tab-panel" id="tab-history">
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>App</th>
                        <th>Device / IP</th>
                        <th>Status</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <tr><td colspan="4" style="color:var(--text-muted);text-align:center;padding:40px">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── Add App Modal ── -->
<div class="modal-backdrop" id="addModal">
    <div class="modal">
        <h2>Add New Integration</h2>
        <p>Paste the app’s login URL. You will get a SecureTS login link and a secret key to use on that page.</p>
        <div class="modal-error" id="addError"></div>
        <div class="form-field">
            <label for="appName">App Name</label>
            <input type="text" id="appName" placeholder="e.g. Google Workspace">
        </div>
        <div class="form-field">
            <label for="appUrl">App URL</label>
            <input type="url" id="appUrl" placeholder="https://accounts.google.com">
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-primary" onclick="addApp()">Add Integration</button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const flashEl = document.getElementById('flash');

function setFlash(type, message) {
    flashEl.hidden = false;
    flashEl.className = 'flash ' + type;
    flashEl.textContent = message;
}

function showTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
    if (name === 'history') loadHistory();
}

function openModal() {
    document.getElementById('addError').textContent = '';
    document.getElementById('addModal').classList.add('open');
}
function closeModal() { document.getElementById('addModal').classList.remove('open'); }

async function addApp() {
    const name = document.getElementById('appName').value.trim();
    const url  = document.getElementById('appUrl').value.trim();
    const errEl = document.getElementById('addError');
    errEl.textContent = '';
    if (!name || !url) { errEl.textContent = 'Please enter both the app name and login URL.'; return; }

    const btn = document.querySelector('#addModal .btn-primary');
    btn.disabled = true; btn.textContent = 'Adding…';
    setFlash('loading', 'Connecting this app to your account…');

    try {
        const res = await fetch('/web/integrations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ name, url }),
        });
        const body = await res.json().catch(() => ({}));
        if (res.ok) {
            sessionStorage.setItem('hubFlash', JSON.stringify({
                type: 'success',
                message: 'App connected. Copy the login link and secret for this account.',
            }));
            window.location.reload();
            return;
        }
        const firstError = body.errors ? Object.values(body.errors)[0][0] : null;
        errEl.textContent = firstError || body.message || 'Could not add this app. Check the URL and try again.';
        setFlash('error', errEl.textContent);
    } catch (e) {
        errEl.textContent = 'Network error – please check your connection.';
        setFlash('error', errEl.textContent);
    } finally {
        btn.disabled = false; btn.textContent = 'Add Integration';
    }
}

async function removeApp(id) {
    if (!confirm('Remove this integration?')) return;
    setFlash('loading', 'Removing integration…');
    try {
        const res = await fetch(`/web/integrations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error();
        document.getElementById('card-' + id)?.remove();
        setFlash('success', 'Integration removed from this account.');
        if (!document.querySelector('.app-card')) window.location.reload();
    } catch (_) {
        setFlash('error', 'Could not remove this integration.');
    }
}

async function toggleApp(id, checkbox) {
    const previous = checkbox.checked;
    try {
        const res = await fetch(`/web/integrations/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error();
        setFlash('success', previous ? 'App enabled for this account.' : 'App disabled for this account.');
    } catch (_) {
        checkbox.checked = !previous;
        setFlash('error', 'Could not update this app.');
    }
}

function copyText(value, message) {
    navigator.clipboard.writeText(value).then(() => setFlash('success', message)).catch(() => setFlash('error', 'Could not copy.'));
}

// ── TOTP Timer ───────────────────────────────────────────────────────────────
async function loadTotpForApp(appId) {
    try {
        const res  = await fetch(`/web/integrations/${appId}/totp`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        const codeEl  = document.getElementById('code-' + appId);
        const timerEl = document.getElementById('timer-' + appId);
        const barEl   = document.getElementById('bar-' + appId);
        if (codeEl) {
            codeEl.textContent = data.code ?? '------';
            const pct = ((data.expires_in ?? 30) / 30) * 100;
            if (barEl) barEl.style.width = pct + '%';
            if (timerEl) timerEl.textContent = (data.expires_in ?? 30) + 's';
        }
    } catch (_) {}
}

// Poll TOTP every second for all visible app cards
function startTotpPolling() {
    const cards = document.querySelectorAll('[id^="totp-"]');
    cards.forEach(el => {
        const id = el.id.replace('totp-', '');
        loadTotpForApp(id);
    });
    setInterval(() => {
        cards.forEach(el => {
            const id = el.id.replace('totp-', '');
            loadTotpForApp(id);
        });
    }, 1000);
}

// ── Load Login History ───────────────────────────────────────────────────────
async function loadHistory() {
    const tbody = document.getElementById('historyBody');
    tbody.innerHTML = '<tr><td colspan="4" style="color:var(--text-muted);text-align:center;padding:40px">Loading login history…</td></tr>';
    try {
        const res  = await fetch('/web/login-events', {
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="color:var(--text-muted);text-align:center;padding:40px">No login history yet for this account.</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(e => `
            <tr>
                <td><strong>${e.integration?.name ?? '–'}</strong></td>
                <td style="color:var(--text-muted)">${e.device ?? ''} ${e.ip_address ? '· ' + e.ip_address : ''}</td>
                <td><span class="badge ${e.status}">${e.status}</span></td>
                <td style="color:var(--text-muted)">${new Date(e.created_at).toLocaleString()}</td>
            </tr>
        `).join('');
    } catch (_) {
        tbody.innerHTML = '<tr><td colspan="4" style="color:#F87171;text-align:center;padding:40px">Could not load login history.</td></tr>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = sessionStorage.getItem('hubFlash');
    if (saved) {
        sessionStorage.removeItem('hubFlash');
        try {
            const flash = JSON.parse(saved);
            setFlash(flash.type, flash.message);
        } catch (_) {}
    }
    startTotpPolling();
    document.getElementById('addModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
});
</script>

</body>
</html>
