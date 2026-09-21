<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_user_can_login_and_receive_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile@securets.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'mobile@securets.com',
            'password' => 'secret123',
            'device_name' => 'iphone_15_pro',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $token = $response->json('token');

        // Test protected user-profile endpoint with Bearer token
        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user-profile');

        $profileResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user' => [
                    'email' => 'mobile@securets.com',
                ],
            ]);

        // Test protected dashboard-data endpoint
        $dashboardResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard-data');

        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'metrics' => [
                    'threats_blocked',
                    'active_shields',
                    'network_security',
                ],
            ]);
    }
}
