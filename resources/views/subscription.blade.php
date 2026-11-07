<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subscription – SecureThreatShield</title>
    <meta name="description" content="Manage your SecureThreatShield subscription plan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-blue:   #3D5AF1;
            --brand-dark:   #0F172A;
            --brand-mid:    #1E293B;
            --brand-border: rgba(255,255,255,0.08);
            --text-primary: #F8FAFC;
            --text-muted:   #94A3B8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--brand-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        @include('account.navigation-styles')

        /* ── Page layout ── */
        .page { max-width: 1100px; margin: 0 auto; padding: 48px 32px; }

        .page-header {
            margin-bottom: 40px; display: flex; align-items: flex-start;
            justify-content: space-between; gap: 16px; flex-wrap: wrap;
        }
        .page-header h1 { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
        .page-header p  { font-size: 14px; color: var(--text-muted); }

        /* ── Flash messages ── */
        .flash {
            margin-bottom: 24px; border-radius: 10px; padding: 14px 18px;
            font-size: 14px; font-weight: 500;
        }
        .flash.success { background: rgba(34,197,94,0.12);  color: #4ADE80; }
        .flash.error   { background: rgba(239,68,68,0.12);  color: #F87171; }
        .flash.loading { background: rgba(61,90,241,0.12);  color: #818CF8; }

        /* ── Plans grid ── */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .plan-card {
            background: var(--brand-mid);
            border: 2px solid var(--brand-border);
            border-radius: 20px; padding: 28px 24px;
            display: flex; flex-direction: column;
            position: relative; overflow: hidden;
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }
        .plan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
        .plan-card.current {
            border-color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent), 0 8px 32px rgba(0,0,0,0.25);
        }

        /* Accent top bar */
        .plan-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: var(--accent);
            border-radius: 20px 20px 0 0;
        }

        /* ── Current badge ── */
        .current-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 3px 10px;
            font-size: 11px; font-weight: 600; color: var(--accent);
            margin-bottom: 16px; width: fit-content;
        }

        .plan-name {
            font-size: 20px; font-weight: 800; margin-bottom: 6px;
            color: var(--accent);
        }
        .plan-price {
            font-size: 32px; font-weight: 800; margin-bottom: 4px;
        }
        .plan-price span { font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .plan-limit {
            font-size: 13px; color: var(--text-muted); margin-bottom: 24px;
        }

        /* Feature list */
        .plan-features { list-style: none; flex: 1; margin-bottom: 24px; }
        .plan-features li {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-muted); margin-bottom: 10px;
        }
        .plan-features li::before {
            content: '✓'; font-size: 12px; font-weight: 700;
            color: var(--accent); flex-shrink: 0;
        }

        /* Buttons */
        .btn-subscribe {
            width: 100%; padding: 13px; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            font-family: 'Inter', sans-serif;
            background: var(--accent); color: #fff;
            transition: filter 0.2s, transform 0.15s;
        }
        .btn-subscribe:hover:not(:disabled) { filter: brightness(1.15); transform: translateY(-1px); }
        .btn-subscribe:disabled {
            background: rgba(255,255,255,0.08); color: var(--text-muted);
            cursor: default; transform: none; filter: none;
        }
        .btn-current {
            width: 100%; padding: 13px; border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 12px; font-size: 14px; font-weight: 700;
            background: transparent; color: var(--text-muted); cursor: default;
            font-family: 'Inter', sans-serif;
        }

        /* Manage billing */
        .btn-manage {
            background: rgba(255,255,255,0.06); color: var(--text-primary);
            border: 1px solid var(--brand-border); border-radius: 10px;
            padding: 11px 18px; font-size: 13px; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer; white-space: nowrap;
            transition: background 0.2s;
        }
        .btn-manage:hover:not(:disabled) { background: rgba(255,255,255,0.1); }
        .btn-manage:disabled { opacity: 0.6; cursor: default; }

        /* Loading spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            display: inline-block; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
            border-radius: 50%; animation: spin 0.7s linear infinite;
            vertical-align: middle; margin-right: 6px;
        }
    </style>
</head>
<body>

@include('account.navigation', ['activeNav' => 'subscription'])
@include('account.profile-menu')

<div class="page">
    <div class="page-header">
        <div>
            <h1>Subscription Plans</h1>
            <p>Choose the plan that fits your security needs. Upgrade at any time.</p>
        </div>
        @if (($subscription ?? null)?->stripe_customer_id)
            <button class="btn-manage" id="btnManageBilling" onclick="manageBilling(this)">
                Manage Billing
            </button>
        @endif
    </div>

    @if (session('flash_success'))
        <div class="flash success" id="flash">{{ session('flash_success') }}</div>
    @elseif (session('flash_error'))
        <div class="flash error" id="flash">{{ session('flash_error') }}</div>
    @else
        <div class="flash loading" id="flash" hidden></div>
    @endif

    <div class="plans-grid">
        @foreach ($plans as $key => $plan)
        @php
            $isCurrent  = $currentPlan === $key;
            $isFree     = $key === 'free';
            $limitLabel = $plan['limit'] ? $plan['limit'] . ' integrations' : 'Unlimited integrations';
        @endphp
        <div class="plan-card {{ $isCurrent ? 'current' : '' }}" style="--accent: {{ $plan['color'] }}">

            @if ($isCurrent)
                <div class="current-badge">✓ Current Plan</div>
            @endif

            <div class="plan-name">{{ $plan['label'] }}</div>
            <div class="plan-price">
                {{ $isFree ? '$0' : explode(' ', $plan['price_label'])[0] }}
                <span>/ month</span>
            </div>
            <div class="plan-limit">{{ $limitLabel }}</div>

            <ul class="plan-features">
                <li>{{ $limitLabel }}</li>
                <li>TOTP Authenticator</li>
                <li>Push Approval Notifications</li>
                @if (!$isFree)
                    <li>Priority support</li>
                @endif
                @if (in_array($key, ['ultra', 'ultra_plus']))
                    <li>Advanced login analytics</li>
                @endif
                @if ($key === 'ultra_plus')
                    <li>API access & webhooks</li>
                    <li>Dedicated account manager</li>
                @endif
            </ul>

            @if ($isCurrent || $isFree)
                <button class="btn-current" disabled>
                    {{ $isCurrent ? '✓ Active' : 'Free Forever' }}
                </button>
            @else
                <button class="btn-subscribe"
                        id="btn-{{ $key }}"
                        onclick="subscribe('{{ $key }}', this)">
                    Upgrade to {{ $plan['label'] }}
                </button>
            @endif
        </div>
        @endforeach
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const flashEl = document.getElementById('flash');

function setFlash(type, msg) {
    flashEl.hidden   = false;
    flashEl.className = 'flash ' + type;
    flashEl.innerHTML = msg;
}

async function subscribe(plan, btn) {
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Redirecting to Stripe…';
    setFlash('loading', 'Opening Stripe Checkout…');

    try {
        const res  = await fetch('/web/subscription/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ plan }),
        });
        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.error || 'Could not start checkout.');
        }

        // Controller returns a redirect for web — but in case it returns JSON URL:
        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        }
    } catch (e) {
        setFlash('error', e.message || 'Something went wrong. Please try again.');
        btn.disabled  = false;
        btn.textContent = originalText;
    }
}

async function manageBilling(btn) {
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Opening…';
    setFlash('loading', 'Opening the billing portal…');

    try {
        const res  = await fetch('/web/subscription/portal', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
        });
        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.error || 'Could not open the billing portal.');
        }

        if (data.portal_url) {
            window.location.href = data.portal_url;
        }
    } catch (e) {
        setFlash('error', e.message || 'Something went wrong. Please try again.');
        btn.disabled    = false;
        btn.textContent = originalText;
    }
}
</script>

</body>
</html>
