<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} – SecureThreatShield</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0F172A; color: #F8FAFC;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 32px; text-align: center;
        }

        .card { max-width: 420px; }
        .icon { font-size: 48px; margin-bottom: 16px; }
        h1 { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
        p  { font-size: 14px; color: #94A3B8; line-height: 22px; margin-bottom: 24px; }
        .btn {
            display: inline-block; background: #3D5AF1; color: #fff;
            text-decoration: none; font-weight: 700; font-size: 14px;
            padding: 13px 22px; border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="card" id="mobile-return" data-redirect="{{ $redirect }}">
        <div class="icon">{{ $icon }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>

        @if ($redirect)
            <a class="btn" href="{{ $redirect }}">Return to App</a>
        @endif
    </div>

    <script>
        // Bounce straight back into the app so the in-app browser closes itself.
        var card     = document.getElementById('mobile-return');
        var redirect = card ? card.dataset.redirect : '';

        if (redirect) {
            window.location.href = redirect;
        }
    </script>
</body>
</html>
