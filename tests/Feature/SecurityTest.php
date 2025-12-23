<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_authentication(): void
    {
        $response = $this->getJson('/api/cart');
        $response->assertStatus(401);
    }

    public function test_admin_routes_require_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_seller_routes_require_seller_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/seller/dashboard');

        $response->assertStatus(403);
    }

    public function test_xss_input_is_sanitized(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/user/profile', [
                'name' => '<script>alert("xss")</script>Test',
                'email' => $user->email,
            ]);

        $response->assertStatus(200);
        $this->assertStringNotContainsString('<script>', $response->json('user.name'));
    }

    public function test_health_endpoint_is_accessible(): void
    {
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'timestamp', 'database']);
    }
}
