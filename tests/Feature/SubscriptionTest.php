<?php

use App\Models\Subscription;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// ── Subscription page ──────────────────────────────────────────────────────────

test('guest is redirected from subscription page', function () {
    $this->get(route('subscription'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view subscription page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('subscription'))
        ->assertOk()
        ->assertSee('Free')
        ->assertSee('Pro')
        ->assertSee('Ultra')
        ->assertSee('Ultra Plus')
        ->assertSee('Subscription Plans');
});

test('subscription nav link appears on integrations page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('integrations'))
        ->assertOk()
        ->assertSee(route('subscription', absolute: false), false);
});

// ── Plan limit enforcement ──────────────────────────────────────────────────────

test('free user can add up to 3 integrations', function () {
    $user = User::factory()->create();

    foreach (range(1, 3) as $i) {
        $this->actingAs($user)
            ->postJson('/web/integrations', [
                'name' => "App {$i}",
                'url'  => "https://app{$i}.example.com",
            ])
            ->assertCreated();
    }
});

test('free user hitting 4th integration gets 403 upgrade_required', function () {
    $user = User::factory()->create();

    foreach (range(1, 3) as $i) {
        $user->integrations()->create([
            'name'       => "App {$i}",
            'url'        => "https://app{$i}.example.com",
            'secret_key' => str_repeat('a', 16),
        ]);
    }

    $this->actingAs($user)
        ->postJson('/web/integrations', [
            'name' => 'App 4',
            'url'  => 'https://app4.example.com',
        ])
        ->assertStatus(403)
        ->assertJsonPath('upgrade_required', true)
        ->assertJsonPath('plan', 'free')
        ->assertJsonPath('limit', 3);
});

test('pro user (limit 10) can add up to 10 integrations before hitting limit', function () {
    $user = User::factory()->create();
    Subscription::create([
        'user_id' => $user->id,
        'plan'    => 'pro',
        'status'  => 'active',
    ]);

    foreach (range(1, 10) as $i) {
        $user->integrations()->create([
            'name'       => "App {$i}",
            'url'        => "https://proapp{$i}.example.com",
            'secret_key' => str_repeat('b', 16),
        ]);
    }

    // 11th should be blocked
    $this->actingAs($user)
        ->postJson('/web/integrations', [
            'name' => 'App 11',
            'url'  => 'https://proapp11.example.com',
        ])
        ->assertStatus(403)
        ->assertJsonPath('upgrade_required', true)
        ->assertJsonPath('plan', 'pro')
        ->assertJsonPath('limit', 10);
});

test('ultra_plus user (unlimited) is never blocked', function () {
    $user = User::factory()->create();
    Subscription::create([
        'user_id' => $user->id,
        'plan'    => 'ultra_plus',
        'status'  => 'active',
    ]);

    // Add 5 integrations quickly — should never get a 403
    foreach (range(1, 5) as $i) {
        $this->actingAs($user)
            ->postJson('/web/integrations', [
                'name' => "Unlimited App {$i}",
                'url'  => "https://unlimited{$i}.example.com",
            ])
            ->assertCreated();
    }
});

// ── Mobile API: subscription status ───────────────────────────────────────────

test('mobile can fetch subscription status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/subscription/status')
        ->assertOk()
        ->assertJsonPath('plan', 'free')
        ->assertJsonPath('limit', 3)
        ->assertJsonStructure(['plan', 'plan_label', 'price_label', 'limit', 'status']);
});

test('mobile status reflects paid plan after subscription row is created', function () {
    $user = User::factory()->create();
    Subscription::create([
        'user_id' => $user->id,
        'plan'    => 'ultra',
        'status'  => 'active',
    ]);
    Sanctum::actingAs($user);

    $this->getJson('/api/subscription/status')
        ->assertOk()
        ->assertJsonPath('plan', 'ultra')
        ->assertJsonPath('limit', 20);
});

// ── Stripe Webhook (no real Stripe calls — webhook secret is empty in test env) ─

test('webhook checkout.session.completed upgrades plan', function () {
    $user = User::factory()->create();

    $payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'metadata'     => ['user_id' => $user->id, 'plan' => 'pro'],
                'subscription' => 'sub_testABC',
                'customer'     => 'cus_testXYZ',
            ],
        ],
    ]);

    $this->postJson('/api/stripe/webhook', json_decode($payload, true))
        ->assertOk()
        ->assertJsonPath('received', true);

    $this->assertDatabaseHas('subscriptions', [
        'user_id'                 => $user->id,
        'plan'                    => 'pro',
        'stripe_subscription_id'  => 'sub_testABC',
        'stripe_customer_id'      => 'cus_testXYZ',
        'status'                  => 'active',
    ]);
});

test('webhook customer.subscription.deleted resets plan to free', function () {
    $user = User::factory()->create();
    Subscription::create([
        'user_id'                => $user->id,
        'plan'                   => 'ultra',
        'stripe_subscription_id' => 'sub_toDelete',
        'status'                 => 'active',
    ]);

    $payload = [
        'type' => 'customer.subscription.deleted',
        'data' => ['object' => ['id' => 'sub_toDelete']],
    ];

    $this->postJson('/api/stripe/webhook', $payload)
        ->assertOk();

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'plan'    => 'free',
        'status'  => 'canceled',
    ]);
});

// ── Mobile return pages (public — no web session in the in-app browser) ─────────

test('mobile success page is public and renders', function () {
    $this->get(route('subscription.mobile-success'))
        ->assertOk()
        ->assertSee('Payment received');
});

test('mobile cancel page is public and renders', function () {
    $this->get(route('subscription.mobile-cancel'))
        ->assertOk()
        ->assertSee('Payment cancelled');
});

test('mobile success page bounces to an allowed app deep link', function () {
    $deepLink = 'exp://192.168.1.5:8081/--/subscription';

    $this->get(route('subscription.mobile-success') . '?redirect=' . urlencode($deepLink))
        ->assertOk()
        ->assertSee($deepLink, false);
});

test('mobile success page drops a non-app redirect target', function () {
    $this->get(route('subscription.mobile-success') . '?redirect=' . urlencode('https://evil.example.com'))
        ->assertOk()
        ->assertDontSee('evil.example.com');
});

// ── Billing portal ─────────────────────────────────────────────────────────────

test('billing portal returns 422 when the user has no Stripe customer', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/subscription/portal')
        ->assertStatus(422)
        ->assertJsonPath('error', 'No billing account yet. Please subscribe to a plan first.');
});
