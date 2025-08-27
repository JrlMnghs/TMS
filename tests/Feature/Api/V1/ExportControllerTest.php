<?php

namespace Tests\Feature\Api\V1;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test export for a specific locale.
     */
    public function test_export_for_specific_locale(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);

        $response = $this->getJson("/api/v1/export/{$locale->code}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'auth.login.title' => 'Login',
            ],
        ]);
    }

    /**
     * Test export with tag filtering.
     */
    public function test_export_with_tag_filtering(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();
        $tag = Tag::factory()->web()->create();

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'web.header.title',
        ]);

        $translationKey->tags()->attach($tag->id);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Welcome',
        ]);

        $response = $this->getJson("/api/v1/export/{$locale->code}?tags=web");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'web.header.title' => 'Welcome',
            ],
        ]);
    }

    /**
     * Test export with multiple tags.
     */
    public function test_export_with_multiple_tags(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();
        $webTag = Tag::factory()->web()->create();
        $authTag = Tag::factory()->auth()->create();

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.button',
        ]);

        $translationKey->tags()->attach([$webTag->id, $authTag->id]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Sign In',
        ]);

        $response = $this->getJson("/api/v1/export/{$locale->code}?tags=web,auth");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'auth.login.button' => 'Sign In',
            ],
        ]);
    }

    /**
     * Test export with streaming for large datasets.
     */
    public function test_export_with_streaming(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        // Create 100 translation keys
        $translationKeys = TranslationKey::factory()->count(100)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $response = $this->getJson("/api/v1/export/{$locale->code}?stream=true");

        $this->assertApiResponse($response, 200);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertCount(100, $response->json('data'));
    }

    /**
     * Test export for non-existent locale.
     */
    public function test_export_for_nonexistent_locale(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/v1/export/nonexistent');

        $this->assertApiErrorResponse($response, 404);
    }

    /**
     * Test export without authentication.
     */
    public function test_export_without_authentication(): void
    {
        $locale = Locale::factory()->english()->create();

        $response = $this->getJson("/api/v1/export/{$locale->code}");

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test export performance with small dataset.
     */
    public function test_export_performance_small_dataset(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        // Create 10 translation keys
        $translationKeys = TranslationKey::factory()->count(10)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $responseTime = $this->measureResponseTime(function () use ($locale) {
            $this->getJson("/api/v1/export/{$locale->code}");
        }, 100); // Small dataset should be very fast

        $this->assertLessThan(100, $responseTime, 'Export response time exceeded 100ms for small dataset');
    }

    /**
     * Test export performance with medium dataset.
     */
    public function test_export_performance_medium_dataset(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        // Create 1000 translation keys
        $translationKeys = TranslationKey::factory()->count(1000)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $responseTime = $this->measureResponseTime(function () use ($locale) {
            $this->getJson("/api/v1/export/{$locale->code}");
        }, 500); // Medium dataset should be under 500ms

        $this->assertLessThan(500, $responseTime, 'Export response time exceeded 500ms for medium dataset');
    }

    /**
     * Test export performance with tag filtering.
     */
    public function test_export_performance_with_tag_filtering(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();
        $tag = Tag::factory()->web()->create();

        // Create 500 translation keys with tags
        $translationKeys = TranslationKey::factory()->count(500)->create();

        foreach ($translationKeys as $key) {
            $key->tags()->attach($tag->id);
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $responseTime = $this->measureResponseTime(function () use ($locale) {
            $this->getJson("/api/v1/export/{$locale->code}?tags=web");
        }, 500); // Tag filtering should still be under 500ms

        $this->assertLessThan(500, $responseTime, 'Export with tag filtering exceeded 500ms');
    }

    /**
     * Test export with empty result set.
     */
    public function test_export_with_empty_result_set(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        $response = $this->getJson("/api/v1/export/{$locale->code}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    /**
     * Test export with invalid tag parameter.
     */
    public function test_export_with_invalid_tag_parameter(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        $response = $this->getJson("/api/v1/export/{$locale->code}?tags=invalid_tag");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    /**
     * Test export with mixed case tags.
     */
    public function test_export_with_mixed_case_tags(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();
        $tag = Tag::factory()->create(['name' => 'Web']);

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'web.header.title',
        ]);

        $translationKey->tags()->attach($tag->id);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Welcome',
        ]);

        $response = $this->getJson("/api/v1/export/{$locale->code}?tags=Web");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'web.header.title' => 'Welcome',
            ],
        ]);
    }

    /**
     * Test export with special characters in locale code.
     */
    public function test_export_with_special_characters_in_locale(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->create([
            'code' => 'en-US',
            'name' => 'ENGLISH_US',
        ]);

        $response = $this->getJson("/api/v1/export/{$locale->code}");

        $this->assertApiResponse($response, 200);
    }

    /**
     * Test concurrent export requests.
     */
    public function test_concurrent_export_requests(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);

        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson("/api/v1/export/{$locale->code}");
        }

        foreach ($responses as $response) {
            $this->assertApiResponse($response, 200);
            $response->assertJson([
                'data' => [
                    'auth.login.title' => 'Login',
                ],
            ]);
        }
    }
}
