<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup basic roles if needed by the app
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_checkout_calculates_actual_total_from_cart()
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');

        $product = Product::factory()->create([
            'price' => 1500,
            'stock' => 10,
            'status' => 'approved'
        ]);

        // Add to cart
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => [
                    'name' => 'Test User',
                    'address' => '123 Street',
                    'city' => 'Test City',
                    'state' => 'Test State',
                    'pincode' => '123456',
                    'phone' => '1234567890',
                    'email' => 'test@example.com'
                ]
            ]);

        $response->assertStatus(201);
        $this->assertEquals(3000, $response->json('order.total_amount'));
        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_cart_prevents_adding_more_than_stock()
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');

        $product = Product::factory()->create([
            'price' => 1500,
            'stock' => 5,
            'status' => 'approved'
        ]);

        // Try to add 6
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 6
            ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Not enough stock available');
    }

    public function test_cart_supports_variations()
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');

        $product = Product::factory()->create([
            'price' => 1500,
            'stock' => 10,
            'status' => 'approved'
        ]);

        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Large',
            'price' => 1600,
            'stock' => 3,
            'is_active' => true
        ]);

        // Add variation to cart
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'quantity' => 1
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 1
        ]);
    }
}
