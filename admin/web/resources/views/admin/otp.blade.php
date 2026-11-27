<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter your code — SecureTS Admin</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-background text-text font-sans antialiased min-h-screen flex items-center justify-center p-6">
    <main class="w-full max-w-sm bg-surface border border-border rounded-xl p-6 flex flex-col gap-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center font-semibold text-sm tracking-tight">TS</div>
            <div>
                <h1 class="text-base font-semibold text-text">Check your email</h1>
                <p class="text-xs text-text-muted">We sent a 6-digit code to <span class="text-text">{{ $maskedEmail }}</span></p>
            </div>
        </div>

        @if (session('status'))
            <p class="text-xs text-emerald p-3 rounded-lg border border-border" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.otp.verify') }}" class="flex flex-col gap-4">
            @csrf
            <label class="flex flex-col gap-1.5 text-sm text-text-muted">
                Sign-in code
                <input type="text" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code"
                       pattern="[0-9 ]{6,7}" maxlength="7" placeholder="000000"
                       class="min-h-11 px-3 py-2 rounded-lg bg-background border border-border text-lg text-text tracking-[0.4em] font-mono text-center focus:outline-none focus:border-primary">
            </label>

            <p class="text-xs text-text-muted" id="otp-expiry"
               data-expires-at="{{ $expiresAt?->toIso8601String() }}">
                The code expires {{ $expiresAt ? 'at '.$expiresAt->timezone(config('app.timezone'))->format('g:i:s A') : "in {$ttlMinutes} minutes" }}.
            </p>

            @error('code')
                <p class="text-xs text-crimson" role="alert">{{ $message }}</p>
            @enderror

            <button type="submit" class="min-h-11 px-4 py-2 rounded-lg bg-primary text-text text-sm font-medium transition-colors duration-150 ease-out">Verify and sign in</button>
        </form>

        <div class="flex items-center justify-between text-xs text-text-muted">
            <form method="POST" action="{{ route('admin.otp.resend') }}">
                @csrf
                <button type="submit" id="otp-resend" data-wait="{{ $resendWait }}" class="hover:text-text disabled:opacity-50 disabled:cursor-not-allowed">Send a new code</button>
            </form>
            <a href="{{ route('admin.login') }}" class="hover:text-text">Use a different account</a>
        </div>
    </main>

    <script>
        (() => {
            const expiry = document.getElementById('otp-expiry');
            const expiresAt = expiry?.dataset.expiresAt ? new Date(expiry.dataset.expiresAt) : null;
            const resend = document.getElementById('otp-resend');
            let wait = Number(resend?.dataset.wait || 0);

            const tick = () => {
                if (expiresAt) {
                    const left = Math.max(0, Math.round((expiresAt - Date.now()) / 1000));
                    expiry.textContent = left > 0
                        ? `The code expires in ${Math.floor(left / 60)}:${String(left % 60).padStart(2, '0')}.`
                        : 'The code has expired. Send a new code to continue.';
                }
                if (resend) {
                    resend.disabled = wait > 0;
                    resend.textContent = wait > 0 ? `Send a new code (${wait}s)` : 'Send a new code';
                    wait = Math.max(0, wait - 1);
                }
            };

            tick();
            setInterval(tick, 1000);
        })();
    </script>
</body>
</html>
