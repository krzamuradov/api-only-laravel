<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'device_name' => 'tests',
        ]);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'bob@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'tests',
        ]);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token', 'token_type', 'user'])
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_user_can_get_profile_and_logout_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('tests')->plainTextToken;

        $profileResponse = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $profileResponse
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.email', $user->email);

        $logoutResponse = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'invalid@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(['message', 'errors']);
    }
}
