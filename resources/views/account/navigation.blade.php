<nav class="app-nav">
    <a class="nav-logo" href="{{ route('dashboard') }}" aria-label="SecureThreatShield home">
        <img src="{{ asset('images/securets-logo.png') }}" alt="SecureThreatShield">
    </a>
    <div class="nav-links">
        <a href="{{ route('dashboard') }}" @class(['active' => ($activeNav ?? null) === 'dashboard'])>Dashboard</a>
        <a href="{{ route('integrations') }}" @class(['active' => ($activeNav ?? null) === 'integrations'])>Integrations</a>
    </div>
    <div class="nav-right">
        <button type="button" class="user-avatar" id="profile-trigger" aria-label="Open profile" aria-expanded="false">
            {{ Auth::user()->initials() }}
        </button>
    </div>
</nav>
