<?php

namespace Tests\Feature\Api\V1;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing translations.
     */
    public function test_can_list_translations(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->createTranslationData(5);

        $response = $this->getJson('/api/v1/translations');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'key_name',
                        'description',
                        'tags',
                        'translations',
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }

    /**
     * Test listing translations with pagination.
     */
    public function test_can_list_translations_with_pagination(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->createTranslationData(25);

        $response = $this->getJson('/api/v1/translations?per_page=10');

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'per_page' => 10,
                'total' => 25,
            ],
        ]);
    }

    /**
     * Test listing translations with tag filtering.
     */
    public function test_can_list_translations_with_tag_filter(): void
    {
        $user = $this->createAuthenticatedUser();
        $tag = Tag::factory()->web()->create();

        $translationKey = TranslationKey::factory()->create();
        $translationKey->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/translations?tags=web');

        $this->assertApiResponse($response, 200);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test listing translations with keyword search.
     */
    public function test_can_list_translations_with_keyword_search(): void
    {
        $user = $this->createAuthenticatedUser();
        $locale = Locale::factory()->english()->create();

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login to your account',
        ]);

        $response = $this->getJson('/api/v1/translations?keyword=login');

        $this->assertApiResponse($response, 200);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test showing a specific translation.
     */
    public function test_can_show_translation(): void
    {
        $user = $this->createAuthenticatedUser();
        $translationKey = $this->createTranslationData(1)->first();

        $response = $this->getJson("/api/v1/translations/{$translationKey->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'id' => $translationKey->id,
                'key_name' => $translationKey->key_name,
            ],
        ]);
    }

    /**
     * Test showing non-existent translation.
     */
    public function test_cannot_show_nonexistent_translation(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/v1/translations/99999');

        $this->assertApiErrorResponse($response, 404);
    }

    /**
     * Test creating a new translation.
     */
    public function test_can_create_translation(): void
    {
        $user = $this->createAuthenticatedUser();

        $data = [
            'key_name' => 'auth.login.title',
            'values' => [
                'en' => 'Login',
                'fr' => 'Connexion',
            ],
            'tags' => ['auth', 'web'],
        ];

        $response = $this->postJson('/api/v1/translations', $data);

        $this->assertApiResponse($response, 201);
        $response->assertJson([
            'data' => [
                'key_name' => 'auth.login.title',
            ],
        ]);

        $this->assertDatabaseHas('translation_keys', [
            'key_name' => 'auth.login.title',
        ]);
    }

    /**
     * Test creating translation with validation errors.
     */
    public function test_cannot_create_translation_with_invalid_data(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/translations', [
            'key_name' => '',
            'values' => [],
        ]);

        $this->assertApiErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['key_name', 'values']);
    }

    /**
     * Test creating translation with duplicate key name.
     */
    public function test_cannot_create_translation_with_duplicate_key(): void
    {
        $user = $this->createAuthenticatedUser();
        $existingKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        $response = $this->postJson('/api/v1/translations', [
            'key_name' => 'auth.login.title',
            'values' => [
                'en' => 'Login',
            ],
        ]);

        $this->assertApiErrorResponse($response, 422);
    }

    /**
     * Test updating a translation.
     */
    public function test_can_update_translation(): void
    {
        $user = $this->createAuthenticatedUser();
        $translationKey = $this->createTranslationData(1)->first();

        $updateData = [
            'key_name' => 'auth.login.updated',
            'values' => [
                'en' => 'Updated Login',
            ],
            'tags' => ['auth', 'updated'],
        ];

        $response = $this->putJson("/api/v1/translations/{$translationKey->id}", $updateData);

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'data' => [
                'key_name' => 'auth.login.updated',
            ],
        ]);
    }

    /**
     * Test updating non-existent translation.
     */
    public function test_cannot_update_nonexistent_translation(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->putJson('/api/v1/translations/99999', [
            'key_name' => 'auth.login.updated',
            'values' => ['en' => 'Updated Login'],
        ]);

        $this->assertApiErrorResponse($response, 404);
    }

    /**
     * Test deleting a translation.
     */
    public function test_can_delete_translation(): void
    {
        $user = $this->createAuthenticatedUser();
        $translationKey = $this->createTranslationData(1)->first();

        $response = $this->deleteJson("/api/v1/translations/{$translationKey->id}");

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'deleted' => true,
        ]);

        $this->assertDatabaseMissing('translation_keys', [
            'id' => $translationKey->id,
        ]);
    }

    /**
     * Test deleting non-existent translation.
     */
    public function test_cannot_delete_nonexistent_translation(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->deleteJson('/api/v1/translations/99999');

        $this->assertApiResponse($response, 200);
        $response->assertJson([
            'deleted' => false,
        ]);
    }

    /**
     * Test listing translations without authentication.
     */
    public function test_cannot_list_translations_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/translations');

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test creating translation without authentication.
     */
    public function test_cannot_create_translation_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/translations', [
            'key_name' => 'auth.login.title',
            'values' => ['en' => 'Login'],
        ]);

        $this->assertApiErrorResponse($response, 401);
    }

    /**
     * Test translation listing performance.
     */
    public function test_translation_listing_performance(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->createTranslationData(100);

        $responseTime = $this->measureResponseTime(function () {
            $this->getJson('/api/v1/translations?per_page=20');
        }, 300); // Listing should be under 300ms

        $this->assertLessThan(300, $responseTime, 'Translation listing exceeded 300ms');
    }

    /**
     * Test translation creation performance.
     */
    public function test_translation_creation_performance(): void
    {
        $user = $this->createAuthenticatedUser();

        $responseTime = $this->measureResponseTime(function () {
            $this->postJson('/api/v1/translations', [
                'key_name' => 'auth.login.title',
                'values' => ['en' => 'Login'],
                'tags' => ['auth'],
            ]);
        }, 200); // Creation should be under 200ms

        $this->assertLessThan(200, $responseTime, 'Translation creation exceeded 200ms');
    }

    /**
     * Test translation update performance.
     */
    public function test_translation_update_performance(): void
    {
        $user = $this->createAuthenticatedUser();
        $translationKey = $this->createTranslationData(1)->first();

        $responseTime = $this->measureResponseTime(function () use ($translationKey) {
            $this->putJson("/api/v1/translations/{$translationKey->id}", [
                'key_name' => 'auth.login.updated',
                'values' => ['en' => 'Updated Login'],
            ]);
        }, 200); // Update should be under 200ms

        $this->assertLessThan(200, $responseTime, 'Translation update exceeded 200ms');
    }

    /**
     * Test concurrent translation operations.
     */
    public function test_concurrent_translation_operations(): void
    {
        $user = $this->createAuthenticatedUser();
        $translationKey = $this->createTranslationData(1)->first();

        // Concurrent reads
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson("/api/v1/translations/{$translationKey->id}");
        }

        foreach ($responses as $response) {
            $this->assertApiResponse($response, 200);
        }
    }

    /**
     * Test translation with multiple locales.
     */
    public function test_can_create_translation_with_multiple_locales(): void
    {
        $user = $this->createAuthenticatedUser();

        $data = [
            'key_name' => 'auth.login.title',
            'values' => [
                'en' => 'Login',
                'fr' => 'Connexion',
                'es' => 'Iniciar sesión',
            ],
            'tags' => ['auth', 'web'],
        ];

        $response = $this->postJson('/api/v1/translations', $data);

        $this->assertApiResponse($response, 201);

        // Verify all translations were created
        $this->assertDatabaseHas('translations', [
            'value' => 'Login',
        ]);
        $this->assertDatabaseHas('translations', [
            'value' => 'Connexion',
        ]);
        $this->assertDatabaseHas('translations', [
            'value' => 'Iniciar sesión',
        ]);
    }

    /**
     * Test translation with special characters.
     */
    public function test_can_create_translation_with_special_characters(): void
    {
        $user = $this->createAuthenticatedUser();

        $data = [
            'key_name' => 'auth.login.title',
            'values' => [
                'en' => 'Login & Sign Up',
                'fr' => 'Connexion & Inscription',
            ],
            'tags' => ['auth'],
        ];

        $response = $this->postJson('/api/v1/translations', $data);

        $this->assertApiResponse($response, 201);
        $response->assertJson([
            'data' => [
                'key_name' => 'auth.login.title',
            ],
        ]);
    }
}
