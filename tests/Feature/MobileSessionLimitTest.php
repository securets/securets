<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('mobile login returns the shared session limit and stores token expiry', function () {
    config()->set('sanctum.expiration', 120);

    $user = User::factory()->create([
        'email' => 'mobile@securets.com',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/mobile/login', [
        'email' => 'mobile@securets.com',
        'password' => 'secret123',
        'device_name' => 'iphone_15_pro',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'expires_at',
            'expires_in_minutes',
            'user' => ['id', 'name', 'email'],
        ])
        ->assertJsonPath('expires_in_minutes', 120);

    [$tokenId] = explode('|', $response->json('token'), 2);

    $accessToken = PersonalAccessToken::find($tokenId);

    expect($accessToken)->not->toBeNull();
    expect($accessToken->expires_at)->not->toBeNull();
    expect($accessToken->expires_at->betweenIncluded(
        now()->addMinutes(119),
        now()->addMinutes(121),
    ))->toBeTrue();
});

test('web login page shows the standard sign-in window', function () {
    config()->set('session.lifetime', 120);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('stays signed in for up to 120 minutes after the', false);
});
