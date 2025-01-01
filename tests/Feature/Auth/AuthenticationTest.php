<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
          'name' => 'Test User',
          'email' => 'test@example.com',
          'password' => 'password123',
          'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
          ->assertJsonStructure([
            'user' => [
              'id',
              'name',
              'email',
              'created_at',
              'updated_at',
            ],
            'message',
          ]);

        $this->assertDatabaseHas('users', [
          'email' => 'test@example.com',
          'name' => 'Test User',
        ]);
    }

    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
          ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
          'email' => 'test@example.com',
          'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
          'email' => 'test@example.com',
          'password' => 'password123',
        ]);

        $response->assertOk()
          ->assertJsonStructure([
            'user' => [
              'id',
              'name',
              'email',
            ],
            'message',
          ]);
    }

    public function test_user_cannot_login_with_invalid_data(): void
    {
        $user = User::factory()->create([
          'email' => 'test@example.com',
          'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
          'email' => 'test@example.com',
          'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', [
              'email' => 'test@example.com',
              'password' => 'password123',
            ]);
        }

        $response = $this->postJson('/api/login', [
          'email' => 'test@example.com',
          'password' => 'password123',
        ]);

        $response->assertStatus(429);
    }

    public function test_authenticated_user_can_retrieve_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
          ->getJson('/api/user');

        $response->assertOk()
          ->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
          ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
          ->postJson('/api/logout');

        $response->assertOk()
          ->assertJson([
            'message' => 'Logged out successfully',
          ]);

        $this->refreshApplication();

        $this->getJson('/api/user')
          ->assertUnauthorized();
    }
}
