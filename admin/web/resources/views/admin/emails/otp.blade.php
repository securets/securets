<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your SecureTS admin sign-in code</title>
</head>
<body style="margin:0;padding:24px;background:#0B0E18;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#FFFFFF;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="420" cellpadding="0" cellspacing="0" style="max-width:420px;background:#141829;border:1px solid #232942;border-radius:12px;">
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 4px;font-size:11px;letter-spacing:.18em;font-weight:700;color:#6473A0;">SECURETS ADMIN</p>
                            <h1 style="margin:0 0 16px;font-size:18px;font-weight:600;">Hi {{ $name }}, here is your sign-in code</h1>
                            <p style="margin:0 0 20px;font-size:34px;letter-spacing:.35em;font-weight:700;font-family:Consolas,Menlo,monospace;color:#FFFFFF;">{{ $code }}</p>
                            <p style="margin:0 0 8px;font-size:13px;color:#AAB5CF;">This code expires in {{ $ttl }} {{ $ttl === 1 ? 'minute' : 'minutes' }} and can only be used once.</p>
                            @if ($ipAddress)
                                <p style="margin:0 0 8px;font-size:12px;color:#74809F;">Requested from IP address {{ $ipAddress }}.</p>
                            @endif
                            <p style="margin:16px 0 0;font-size:12px;color:#74809F;">If you did not try to sign in, someone may know your admin password. Change it right away.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
