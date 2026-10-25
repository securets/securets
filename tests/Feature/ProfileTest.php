<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

test('guests are redirected to login when trying to view profile', function () {
    $this->get(route('profile'))
        ->assertRedirect(route('login'));
});

test('profile page is displayed for authenticated user', function () {
    $user = User::factory()->create([
        'name'  => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('Profile details')
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});

test('profile information can be updated', function () {
    $user = User::factory()->create([
        'name'  => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

    $response->assertSessionHas('status')
        ->assertRedirect();

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com');
});

test('email must be unique when updating profile', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $user = User::factory()->create([
        'email' => 'current@example.com',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name'  => 'Current User',
            'email' => 'existing@example.com',
        ]);

    $response->assertSessionHasErrors('email');
    expect($user->fresh()->email)->toBe('current@example.com');
});

test('user can keep same email when updating profile name', function () {
    $user = User::factory()->create([
        'name'  => 'Original Name',
        'email' => 'same@example.com',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name'  => 'New Name Only',
            'email' => 'same@example.com',
        ]);

    $response->assertSessionHas('status');
    expect($user->fresh()->name)->toBe('New Name Only')
        ->and($user->fresh()->email)->toBe('same@example.com');
});

test('user can update password with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
    ]);

    $response = $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password'      => 'old-password-123',
            'password'              => 'new-secure-password-456',
            'password_confirmation' => 'new-secure-password-456',
        ]);

    $response->assertSessionHas('password_status')
        ->assertRedirect();

    expect(Hash::check('new-secure-password-456', $user->fresh()->password))->toBeTrue();
});

test('password update fails when current password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password-123'),
    ]);

    $response = $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password'      => 'incorrect-password',
            'password'              => 'new-secure-password-456',
            'password_confirmation' => 'new-secure-password-456',
        ]);

    $response->assertSessionHasErrors('current_password');
    expect(Hash::check('correct-password-123', $user->fresh()->password))->toBeTrue();
});

test('user can archive account and is logged out', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password-123'),
    ]);

    $response = $this->actingAs($user)
        ->post(route('profile.archive'), [
            'password' => 'password-123',
        ]);

    $response->assertRedirect(route('login'))
        ->assertSessionHas('success');

    expect(Auth::check())->toBeFalse()
        ->and($user->fresh()->isArchived())->toBeTrue();
});

test('archived user account is automatically reactivated upon login', function () {
    $user = User::factory()->create([
        'email'       => 'archived@example.com',
        'password'    => Hash::make('password-123'),
        'archived_at' => now()->subDays(5),
    ]);

    expect($user->isArchived())->toBeTrue();

    $response = $this->post('/login', [
        'email'    => 'archived@example.com',
        'password' => 'password-123',
    ]);

    $response->assertRedirect(route('integrations'));
    $response->assertSessionHas('status');

    $user->refresh();
    expect($user->isArchived())->toBeFalse();
});
