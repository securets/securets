<?php

use App\Models\Device;
use App\Models\Integration;
use App\Models\User;

test('guests are redirected to login when trying to view settings', function () {
    $this->get(route('settings'))
        ->assertRedirect(route('login'));
});

test('settings page is displayed for authenticated user with stats', function () {
    $user = User::factory()->create([
        'preferences' => [
            'email_notifications' => true,
            'mobile_push'         => false,
            'theme'               => 'dark',
        ],
    ]);

    Device::create([
        'user_id'          => $user->id,
        'device_name'      => 'Test iPhone',
        'expo_push_token'  => 'ExponentPushToken[xyz]',
        'last_seen_at'     => now(),
    ]);

    Integration::create([
        'user_id'     => $user->id,
        'name'        => 'Test App',
        'url'         => 'https://example.com',
        'secret_key'  => 'secret_12345',
        'gate_token'  => 'token_12345',
        'is_active'   => true,
    ]);

    $this->actingAs($user)
        ->get(route('settings'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Account Security')
        ->assertSee('1 device')
        ->assertSee('1 app');
});

test('user can update settings preferences', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('settings.update'), [
            'email_notifications' => '1',
            'mobile_push'         => '0',
            'theme'               => 'light',
        ]);

    $response->assertSessionHas('status')
        ->assertRedirect();

    $user->refresh();
    expect($user->preferences['email_notifications'])->toBeTrue()
        ->and($user->preferences['mobile_push'])->toBeFalse()
        ->and($user->preferences['theme'])->toBe('light');
});

test('invalid theme value is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('settings.update'), [
            'theme' => 'unsupported-theme',
        ]);

    $response->assertSessionHasErrors('theme');
});
