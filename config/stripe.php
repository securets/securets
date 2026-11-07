<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    */
    'key'             => env('STRIPE_KEY', ''),
    'secret'          => env('STRIPE_SECRET', ''),
    'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Stripe Price IDs (set manually in .env)
    |--------------------------------------------------------------------------
    */
    'prices' => [
        'pro'        => env('STRIPE_PRICE_PRO', ''),
        'ultra'      => env('STRIPE_PRICE_ULTRA', ''),
        'ultra_plus' => env('STRIPE_PRICE_ULTRA_PLUS', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan Limits (number of integrations allowed per plan)
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'free'       => 3,
        'pro'        => 10,
        'ultra'      => 20,
        'ultra_plus' => null, // unlimited
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan Display Labels & Prices
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'free' => [
            'label'       => 'Free',
            'price_label' => '$0 / month',
            'limit'       => 3,
            'color'       => '#64748B',
        ],
        'pro' => [
            'label'       => 'Pro',
            'price_label' => '$5 / month',
            'limit'       => 10,
            'color'       => '#3D5AF1',
        ],
        'ultra' => [
            'label'       => 'Ultra',
            'price_label' => '$10 / month',
            'limit'       => 20,
            'color'       => '#8B5CF6',
        ],
        'ultra_plus' => [
            'label'       => 'Ultra Plus',
            'price_label' => '$50 / month',
            'limit'       => null,
            'color'       => '#F59E0B',
        ],
    ],
];
