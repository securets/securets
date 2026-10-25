<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Details – SecureThreatShield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --blue: #3D5AF1;
            --blue-hover: #2E46DB;
            --dark: #0F172A;
            --mid: #1E293B;
            --card-bg: #162032;
            --border: rgba(255,255,255,.08);
            --border-focus: rgba(61,90,241,.5);
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
            margin-bottom: 20px;
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
        .card-danger {
            border-color: rgba(239,68,68,0.25);
            background: rgba(239,68,68,0.03);
        }

        .summary { display:flex; align-items:center; gap:18px; }
        .large-avatar {
            width:64px; height:64px; display:grid; place-items:center;
            border-radius:50%; background:var(--blue); font-size:22px; font-weight:800;
            color: #fff; flex-shrink: 0; box-shadow: 0 8px 24px rgba(61,90,241,0.3);
        }
        .summary h2 { margin:0 0 4px; font-size:20px; font-weight:700; }
        .summary p { margin:0; color:var(--muted); font-size:13px; }

        .card-header { margin-bottom: 22px; }
        .card-header h2 { margin:0 0 6px; font-size:17px; font-weight:700; }
        .card-header p { margin:0; color:var(--muted); font-size:13px; line-height:1.5; }

        .form-grid { display: grid; gap: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 7px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #CBD5E1; }
        .form-control {
            background: #0B1120;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(61,90,241,0.25);
        }
        .form-control.is-invalid {
            border-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.2);
        }
        .field-error { color: #FCA5A5; font-size: 12px; margin-top: 3px; }

        .btn-primary {
            background: var(--blue);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 11px 22px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            align-self: flex-start;
        }
        .btn-primary:hover { background: var(--blue-hover); }
        .btn-primary:active { transform: scale(0.98); }

        .btn-danger {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            border-radius: 10px;
            padding: 11px 20px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s;
            align-self: flex-start;
        }
        .btn-danger:hover { background: rgba(239,68,68,0.22); }

        /* Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(4px);
            display: none;
            place-items: center;
            z-index: 500;
            padding: 20px;
        }
        .modal-backdrop.is-open { display: grid; }
        .modal {
            background: #111A2D;
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 16px;
            width: min(440px, 100%);
            padding: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .modal h3 { margin: 0 0 8px; font-size: 18px; color: #F8FAFC; }
        .modal p { margin: 0 0 20px; color: var(--muted); font-size: 13px; line-height: 1.5; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
        .btn-cancel {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 9px 18px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); }

        @media (max-width:640px) {
            main { width:min(100% - 32px, 720px); padding:32px 0; }
            .card { padding:20px; }
            .btn-primary, .btn-danger { width: 100%; justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>
    @include('account.navigation', ['activeNav' => 'profile'])
    @include('account.profile-menu')

    <main>
        <h1>Profile details</h1>
        <p class="intro">Manage your personal details, credentials, and account status.</p>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('password_status'))
            <div class="alert alert-success" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                <span>{{ session('password_status') }}</span>
            </div>
        @endif

        <div class="cards-stack">
            <!-- User Summary Badge -->
            <section class="card">
                <div class="summary">
                    <div class="large-avatar">{{ $user->initials() }}</div>
                    <div>
                        <h2>{{ $user->name }}</h2>
                        <p>{{ $user->email }} &bull; SecureThreatShield account</p>
                    </div>
                </div>
            </section>

            <!-- Profile Info Form -->
            <section class="card">
                <div class="card-header">
                    <h2>Account Information</h2>
                    <p>Update your display name and email address connected to this account.</p>
                </div>
                <form method="POST" action="{{ route('profile.update') }}" class="form-grid">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="name">Display name</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required autocomplete="email">
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </section>

            <!-- Change Password Form -->
            <section class="card">
                <div class="card-header">
                    <h2>Update Password</h2>
                    <p>Ensure your account is using a long, secure password to stay protected.</p>
                </div>
                <form method="POST" action="{{ route('profile.password') }}" class="form-grid">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('current_password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">New password</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" required>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control @error('password_confirmation') is-invalid @enderror"
                               autocomplete="new-password" required>
                        @error('password_confirmation')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">Update Password</button>
                    </div>
                </form>
            </section>

            <!-- Danger Zone: Archive Account -->
            <section class="card card-danger">
                <div class="card-header">
                    <h2 style="color:#FCA5A5">Archive Account</h2>
                    <p>Temporarily deactivate your account. Protection and authentication integrations will be paused. You can reactivate your account at any time simply by logging back in.</p>
                </div>
                <div>
                    <button type="button" class="btn-danger" id="openArchiveModalBtn">Archive Account</button>
                </div>
            </section>
        </div>
    </main>

    <!-- Archive Confirmation Modal -->
    <div class="modal-backdrop" id="archiveModal" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <h3 id="modalTitle">Archive your account?</h3>
            <p>Please enter your current password to confirm you want to archive your account. You can reactivate it anytime by logging back in.</p>
            <form method="POST" action="{{ route('profile.archive') }}" id="archiveForm">
                @csrf
                <div class="form-group">
                    <label for="archive_password">Current password</label>
                    <input type="password" id="archive_password" name="password" class="form-control" required placeholder="Enter password to confirm">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="closeArchiveModalBtn">Cancel</button>
                    <button type="submit" class="btn-danger">Confirm Archive</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('archiveModal');
            const openBtn = document.getElementById('openArchiveModalBtn');
            const closeBtn = document.getElementById('closeArchiveModalBtn');
            const passwordInput = document.getElementById('archive_password');

            const openModal = () => {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                passwordInput.value = '';
                passwordInput.focus();
            };

            const closeModal = () => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            };

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
            });
        })();
    </script>
</body>
</html>
