<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductC_Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_products()
    {
        $admin = User::factory()->create([
            'email' => 'test@example.com',
            'role' => 'admin',
            'password' => Hash::make('password123')
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                [
                    "name" => "Test Product",
                    "sku" => "TP001",
                    "description" => "Test Description",
                    "unit_price" => 100
                ]
            ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['sku' => 'TP001']);
    }
}
