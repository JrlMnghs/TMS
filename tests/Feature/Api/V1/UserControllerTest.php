<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing users.
     */
    public function test_can_list_users(): void
    {
        $user = $this->createAuthenticatedUser();
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/users');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * Test showing a specific user.
     */
    public function test_can_show_user(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$targetUser->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ],
        ]);
    }

    /**
     * Test showing non-existent user.
     */
    public function test_cannot_show_nonexistent_user(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/v1/users/99999');

        $this->assertApiErrorResponse($response, 404);
    }

    /**
     * Test listing users without authentication.
     */
    public function test_cannot_list_users_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/users');

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test showing user without authentication.
     */
    public function test_cannot_show_user_without_authentication(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$targetUser->id}");

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test user listing performance.
     */
    public function test_user_listing_performance(): void
    {
        $user = $this->createAuthenticatedUser();
        User::factory()->count(100)->create();

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson('/api/v1/users');
        }, 200); // User listing should be under 200ms

        $this->assertLessThan(200, $responseTime, 'User listing exceeded 200ms');
    }

    /**
     * Test user show performance.
     */
    public function test_user_show_performance(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create();

        $responseTime = $this->measureResponseTime(function () use ($targetUser) {
            $this->getJson("/api/v1/users/{$targetUser->id}");
        }, 100); // User show should be under 100ms

        $this->assertLessThan(100, $responseTime, 'User show exceeded 100ms');
    }

    /**
     * Test concurrent user operations.
     */
    public function test_concurrent_user_operations(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create();

        // Concurrent reads
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson("/api/v1/users/{$targetUser->id}");
        }

        foreach ($responses as $response) {
            $this->assertApiResponse($response, 200);
        }
    }

    /**
     * Test user with special characters in name.
     */
    public function test_can_show_user_with_special_characters(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create([
            'name' => 'José María O\'Connor-Smith',
        ]);

        $response = $this->getJson("/api/v1/users/{$targetUser->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'name' => 'José María O\'Connor-Smith',
            ],
        ]);
    }

    /**
     * Test user with long email address.
     */
    public function test_can_show_user_with_long_email(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create([
            'email' => 'very.long.email.address.for.testing.purposes@example.com',
        ]);

        $response = $this->getJson("/api/v1/users/{$targetUser->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'email' => 'very.long.email.address.for.testing.purposes@example.com',
            ],
        ]);
    }

    /**
     * Test user listing with pagination.
     */
    public function test_can_list_users_with_pagination(): void
    {
        $user = $this->createAuthenticatedUser();
        User::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/users?per_page=10');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }

    /**
     * Test user listing with search.
     */
    public function test_can_list_users_with_search(): void
    {
        $user = $this->createAuthenticatedUser();
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->getJson('/api/v1/users?search=John');

        $this->assertApiResponse($response, 200);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test user listing with sorting.
     */
    public function test_can_list_users_with_sorting(): void
    {
        $user = $this->createAuthenticatedUser();
        User::factory()->create(['name' => 'Zebra']);
        User::factory()->create(['name' => 'Alpha']);

        $response = $this->getJson('/api/v1/users?sort=name&order=asc');

        $this->assertApiResponse($response, 200);
        $users = $response->json('data.data');
        $this->assertEquals('Alpha', $users[0]['name']);
    }

    /**
     * Test user with different roles.
     */
    public function test_can_show_user_with_role(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $response = $this->getJson("/api/v1/users/{$targetUser->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $targetUser->id,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
        ]);
    }

    /**
     * Test user listing with filtering.
     */
    public function test_can_list_users_with_filtering(): void
    {
        $user = $this->createAuthenticatedUser();
        User::factory()->create(['email' => 'test1@example.com']);
        User::factory()->create(['email' => 'test2@example.com']);

        $response = $this->getJson('/api/v1/users?email=test1@example.com');

        $this->assertApiResponse($response, 200);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test user with created_at and updated_at timestamps.
     */
    public function test_user_includes_timestamps(): void
    {
        $user = $this->createAuthenticatedUser();
        $targetUser = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$targetUser->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
