<?php

namespace Tests\Feature\Performance;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Locale $locale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAuthenticatedUser();
        $this->locale = Locale::factory()->english()->create();
    }

    /**
     * Test export performance with various dataset sizes.
     */
    public function test_export_performance_scalability(): void
    {
        $datasetSizes = [10, 100, 1000, 5000];
        $performanceResults = [];

        foreach ($datasetSizes as $size) {
            $this->createTranslationDataset($size);

            $responseTime = $this->measureResponseTime(function () {
                $this->getJson("/api/v1/export/{$this->locale->code}");
            }, 1000); // Allow up to 1 second for large datasets

            $performanceResults[$size] = $responseTime;

            // Assert performance targets
            if ($size <= 100) {
                $this->assertLessThan(200, $responseTime, "Export for {$size} records exceeded 200ms");
            } elseif ($size <= 1000) {
                $this->assertLessThan(500, $responseTime, "Export for {$size} records exceeded 500ms");
            } else {
                $this->assertLessThan(1000, $responseTime, "Export for {$size} records exceeded 1000ms");
            }
        }

        // Log performance results
        $this->logPerformanceResults('Export Performance', $performanceResults);
    }

    /**
     * Test export with tag filtering performance.
     */
    public function test_export_tag_filtering_performance(): void
    {
        $tag = Tag::factory()->web()->create();
        $this->createTranslationDataset(1000, $tag);

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson("/api/v1/export/{$this->locale->code}?tags=web");
        }, 500);

        $this->assertLessThan(500, $responseTime, 'Export with tag filtering exceeded 500ms');
    }

    /**
     * Test translation listing performance.
     */
    public function test_translation_listing_performance(): void
    {
        $this->createTranslationDataset(1000);

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson('/api/v1/translations?per_page=20');
        }, 300);

        $this->assertLessThan(300, $responseTime, 'Translation listing exceeded 300ms');
    }

    /**
     * Test translation search performance.
     */
    public function test_translation_search_performance(): void
    {
        $this->createTranslationDataset(1000);

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson('/api/v1/translations?keyword=login&per_page=20');
        }, 300);

        $this->assertLessThan(300, $responseTime, 'Translation search exceeded 300ms');
    }

    /**
     * Test translation creation performance.
     */
    public function test_translation_creation_performance(): void
    {
        $responseTime = $this->measureResponseTime(function () {
            $this->postJson('/api/v1/translations', [
                'key_name' => 'auth.login.title',
                'values' => [
                    'en' => 'Login',
                    'fr' => 'Connexion',
                ],
                'tags' => ['auth', 'web'],
            ]);
        }, 200);

        $this->assertLessThan(200, $responseTime, 'Translation creation exceeded 200ms');
    }

    /**
     * Test translation update performance.
     */
    public function test_translation_update_performance(): void
    {
        $translationKey = $this->createTranslationDataset(1)->first();

        $responseTime = $this->measureResponseTime(function () use ($translationKey) {
            $this->putJson("/api/v1/translations/{$translationKey->id}", [
                'key_name' => 'auth.login.updated',
                'values' => [
                    'en' => 'Updated Login',
                ],
                'tags' => ['auth', 'updated'],
            ]);
        }, 200);

        $this->assertLessThan(200, $responseTime, 'Translation update exceeded 200ms');
    }

    /**
     * Test user listing performance.
     */
    public function test_user_listing_performance(): void
    {
        User::factory()->count(100)->create();

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson('/api/v1/users?per_page=20');
        }, 200);

        $this->assertLessThan(200, $responseTime, 'User listing exceeded 200ms');
    }

    /**
     * Test authentication performance.
     */
    public function test_authentication_performance(): void
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
        }, 200);

        $this->assertLessThan(200, $responseTime, 'Authentication exceeded 200ms');
    }

    /**
     * Test concurrent request performance.
     */
    public function test_concurrent_request_performance(): void
    {
        $this->createTranslationDataset(100);

        $startTime = microtime(true);

        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson("/api/v1/export/{$this->locale->code}");
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // All responses should be successful
        foreach ($responses as $response) {
            $this->assertApiResponse($response, 200);
        }

        // Total time should be reasonable for concurrent requests
        $this->assertLessThan(2000, $totalTime, 'Concurrent requests exceeded 2000ms');
    }

    /**
     * Test memory usage during large exports.
     */
    public function test_memory_usage_large_export(): void
    {
        $this->createTranslationDataset(5000);

        $initialMemory = memory_get_usage();

        $response = $this->getJson("/api/v1/export/{$this->locale->code}");

        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease, 'Memory usage exceeded 50MB');

        $this->assertApiResponse($response, 200);
    }

    /**
     * Test streaming export performance.
     */
    public function test_streaming_export_performance(): void
    {
        $this->createTranslationDataset(1000);

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson("/api/v1/export/{$this->locale->code}?stream=true");
        }, 500);

        $this->assertLessThan(500, $responseTime, 'Streaming export exceeded 500ms');
    }

    /**
     * Test database query performance.
     */
    public function test_database_query_performance(): void
    {
        $this->createTranslationDataset(1000);

        $startTime = microtime(true);

        // Execute the same query multiple times to test caching
        for ($i = 0; $i < 5; $i++) {
            $this->getJson("/api/v1/export/{$this->locale->code}");
        }

        $endTime = microtime(true);
        $averageTime = (($endTime - $startTime) * 1000) / 5;

        $this->assertLessThan(500, $averageTime, 'Average query time exceeded 500ms');
    }

    /**
     * Test API response size performance.
     */
    public function test_api_response_size_performance(): void
    {
        $this->createTranslationDataset(100);

        $response = $this->getJson("/api/v1/export/{$this->locale->code}");

        $responseSize = strlen($response->getContent());

        // Response size should be reasonable (less than 1MB for 100 records)
        $this->assertLessThan(1024 * 1024, $responseSize, 'Response size exceeded 1MB');

        $this->assertApiResponse($response, 200);
    }

    /**
     * Create a dataset of translations for testing.
     */
    private function createTranslationDataset(int $count, ?Tag $tag = null): \Illuminate\Database\Eloquent\Collection
    {
        $translationKeys = TranslationKey::factory()->count($count)->create();

        foreach ($translationKeys as $key) {
            if ($tag) {
                $key->tags()->attach($tag->id);
            }

            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $this->locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        return $translationKeys;
    }

    /**
     * Log performance results for analysis.
     */
    private function logPerformanceResults(string $testName, array $results): void
    {
        $logMessage = "\n{$testName} Results:\n";
        foreach ($results as $size => $time) {
            $logMessage .= "  {$size} records: {$time}ms\n";
        }

        // In a real application, you might want to log this to a file or monitoring system
        fwrite(STDERR, $logMessage);
    }

    /**
     * Test API versioning performance impact.
     */
    public function test_api_versioning_performance_impact(): void
    {
        $this->createTranslationDataset(100);

        // Test v1 endpoint
        $v1StartTime = microtime(true);
        $this->getJson("/api/v1/export/{$this->locale->code}");
        $v1Time = (microtime(true) - $v1StartTime) * 1000;

        // Test legacy endpoint
        $legacyStartTime = microtime(true);
        $this->getJson("/api/export/{$this->locale->code}");
        $legacyTime = (microtime(true) - $legacyStartTime) * 1000;

        // Versioning should not significantly impact performance
        $performanceDifference = abs($v1Time - $legacyTime);
        $this->assertLessThan(50, $performanceDifference, 'Versioning caused significant performance impact');
    }

    /**
     * Test error handling performance.
     */
    public function test_error_handling_performance(): void
    {
        $responseTime = $this->measureResponseTime(function () {
            $this->getJson('/api/v1/export/nonexistent');
        }, 100);

        $this->assertLessThan(100, $responseTime, 'Error handling exceeded 100ms');
    }
}
