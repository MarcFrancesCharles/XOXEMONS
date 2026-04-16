<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class WorkFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_auth_workflow(): void
    {
        $userData = [
            'name' => 'Test User',
            'surnames' => 'JUnit',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/register', $userData);
        $registerResponse->assertStatus(201);
        
        $customId = $registerResponse->json('user.custom_id');

        $loginResponse = $this->postJson('/api/login', [
            'custom_id' => $customId,
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('email', 'test@example.com');
    }
}
