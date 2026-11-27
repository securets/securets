<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — SecureTS Admin</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-background text-text font-sans antialiased min-h-screen flex items-center justify-center p-6">
    <main class="w-full max-w-sm bg-surface border border-border rounded-xl p-6 flex flex-col gap-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center font-semibold text-sm tracking-tight">TS</div>
            <div>
                <h1 class="text-base font-semibold text-text">SecureTS Admin</h1>
                <p class="text-xs text-text-muted">Sign in with an administrator account</p>
            </div>
        </div>

        @if ($allowlistEmpty)
            <p class="text-xs text-text-muted p-3 rounded-lg border border-border" role="status">No administrators are configured. Set <span class="font-mono text-text">ADMIN_EMAILS</span> in <span class="font-mono text-text">admin/web/.env</span> to allow sign-in.</p>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="flex flex-col gap-4">
            @csrf
            <label class="flex flex-col gap-1.5 text-sm text-text-muted">
                Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="min-h-11 px-3 py-2 rounded-lg bg-background border border-border text-sm text-text focus:outline-none focus:border-primary">
            </label>
            <label class="flex flex-col gap-1.5 text-sm text-text-muted">
                Password
                <input type="password" name="password" required autocomplete="current-password"
                       class="min-h-11 px-3 py-2 rounded-lg bg-background border border-border text-sm text-text focus:outline-none focus:border-primary">
            </label>

            @error('email')
                <p class="text-xs text-text" role="alert">{{ $message }}</p>
            @enderror

            <button type="submit" class="min-h-11 px-4 py-2 rounded-lg bg-primary text-text text-sm font-medium transition-colors duration-150 ease-out">Continue</button>
            <p class="text-xs text-text-muted">After your password, we email a 6-digit code to finish signing in.</p>
        </form>
    </main>
</body>
</html>
