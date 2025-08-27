<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, WithFaker;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable rate limiting for tests
        $this->withoutRateLimiting();
    }

    /**
     * Create and authenticate a user for testing.
     */
    protected function createAuthenticatedUser(array $attributes = []): \App\Models\User
    {
        $user = \App\Models\User::factory()->create($attributes);
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * Create test data for translations.
     */
    protected function createTranslationData(int $count = 5, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        $locale = \App\Models\Locale::factory()->create(['code' => 'en']);

        return \App\Models\TranslationKey::factory()
            ->count($count)
            ->has(\App\Models\Translation::factory()->state([
                'locale_id' => $locale->id,
                'value' => $this->faker->sentence(),
            ]))
            ->has(\App\Models\Tag::factory()->count(2))
            ->create($attributes);
    }

    /**
     * Assert API response structure.
     *
     * @param  \Illuminate\Testing\TestResponse  $response
     */
    protected function assertApiResponse($response, int $statusCode = 200): void
    {
        $response->assertStatus($statusCode);
        $response->assertHeader('Content-Type', 'application/json');

        if ($statusCode < 400) {
            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'version',
            ]);
            $response->assertJson([
                'success' => true,
                'version' => '1.0.0',
            ]);
        }
    }

    /**
     * Assert API error response structure.
     *
     * @param  \Illuminate\Testing\TestResponse  $response
     */
    protected function assertApiErrorResponse($response, int $statusCode = 400): void
    {
        $response->assertStatus($statusCode);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'success',
            'message',
            'version',
        ]);
        $response->assertJson([
            'success' => false,
            'version' => '1.0.0',
        ]);
    }

    /**
     * Measure API response time.
     */
    protected function measureResponseTime(callable $callback, int $maxTimeMs = 500): float
    {
        $startTime = microtime(true);
        $callback();
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan($maxTimeMs, $responseTime,
            "Response time {$responseTime}ms exceeded maximum {$maxTimeMs}ms");

        return $responseTime;
    }
}
