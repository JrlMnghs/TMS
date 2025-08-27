<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'token',
            ],
            'version',
        ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertApiErrorResponse($response, 401);
        $response->assertJson([
            'message' => 'Invalid credentials',
        ]);
    }

    /**
     * Test login validation errors.
     */
    public function test_login_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $this->assertApiErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test logout for authenticated user.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/logout');

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Test logout without authentication.
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test get current user.
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/v1/user');

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Test get current user without authentication.
     */
    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/user');

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test login performance.
     */
    public function test_login_performance(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $responseTime = $this->measureResponseTime(function () {
            $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);
        }, 200); // Login should be fast

        $this->assertLessThan(200, $responseTime, 'Login response time exceeded 200ms');
    }

    /**
     * Test logout performance.
     */
    public function test_logout_performance(): void
    {
        $user = $this->createAuthenticatedUser();

        $responseTime = $this->measureResponseTime(function () {
            $this->postJson('/api/v1/logout');
        }, 100); // Logout should be very fast

        $this->assertLessThan(100, $responseTime, 'Logout response time exceeded 100ms');
    }

    /**
     * Test concurrent login attempts.
     */
    public function test_concurrent_login_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);
        }

        foreach ($responses as $response) {
            $this->assertApiResponse($response, 200);
        }
    }

    /**
     * Test login with missing fields.
     */
    public function test_login_with_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $this->assertApiErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login with non-existent user.
     */
    public function test_login_with_nonexistent_user(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertApiErrorResponse($response, 401);
        $response->assertJson([
            'message' => 'Invalid credentials',
        ]);
    }
}
