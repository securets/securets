<?php

return [
    // Base URL of the main SecureTS system app, used for the API health check.
    'system_url' => rtrim((string) env('SYSTEM_APP_URL', 'http://localhost:8000'), '/'),

    // Comma-separated allowlist of user emails permitted to use the admin panel.
    // An empty list denies everyone.
    'emails' => array_values(array_filter(array_map(
        fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('ADMIN_EMAILS', ''))
    ))),

    // Lifetime of admin mobile API tokens, in minutes.
    'token_ttl_minutes' => (int) env('ADMIN_TOKEN_TTL_MINUTES', 720),

    /*
    |--------------------------------------------------------------------------
    | Admin web sign-in one-time passcode (OTP)
    |--------------------------------------------------------------------------
    | After a correct password, a 6-digit code is emailed to the admin and must
    | be entered before the session is created.
    */
    'otp' => [
        'ttl_minutes' => (int) env('ADMIN_OTP_TTL_MINUTES', 5),
        'max_attempts' => (int) env('ADMIN_OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('ADMIN_OTP_RESEND_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription tiers
    |--------------------------------------------------------------------------
    | Mirrors web/config/stripe.php ("plans"). Prices are monthly, in cents,
    | and are used to estimate subscription sales on the dashboard.
    */
    'currency' => 'USD',
    'plans' => [
        'free' => ['label' => 'Free', 'price_cents' => 0, 'limit' => 3, 'color' => '#64748B'],
        'pro' => ['label' => 'Pro', 'price_cents' => 500, 'limit' => 10, 'color' => '#3D5AF1'],
        'ultra' => ['label' => 'Ultra', 'price_cents' => 2000, 'limit' => 20, 'color' => '#8B5CF6'],
        'ultra_plus' => ['label' => 'Ultra Plus', 'price_cents' => 5000, 'limit' => null, 'color' => '#F59E0B'],
    ],

    // Stripe secret key (same as /web STRIPE_SECRET). Used only to read the card
    // brand, last 4 digits and expiry for printable purchase receipts.
    'stripe_secret' => (string) env('STRIPE_SECRET', ''),

    // Rows per page for every server-side admin table.
    'per_page' => 20,
];
