<?php

namespace Tests\Unit;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Repositories\TranslationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TranslationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TranslationRepository;
    }

    /**
     * Test search functionality.
     */
    public function test_search_translations(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login to your account',
        ]);

        $results = $this->repository->search([
            'keyword' => 'login',
            'locale' => 'en',
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('auth.login.title', $results->first()->key_name);
    }

    /**
     * Test search with tag filtering.
     */
    public function test_search_with_tag_filtering(): void
    {
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

        $results = $this->repository->search([
            'tags' => 'web',
            'locale' => 'en',
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('web.header.title', $results->first()->key_name);
    }

    /**
     * Test search with key filtering.
     */
    public function test_search_with_key_filtering(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);

        $results = $this->repository->search([
            'key' => 'auth',
            'locale' => 'en',
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('auth.login.title', $results->first()->key_name);
    }

    /**
     * Test find key with relations.
     */
    public function test_find_key_with_relations(): void
    {
        $locale = Locale::factory()->english()->create();
        $tag = Tag::factory()->web()->create();

        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        $translationKey->tags()->attach($tag->id);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);

        $result = $this->repository->findKeyWithRelations($translationKey->id);

        $this->assertEquals('auth.login.title', $result->key_name);
        $this->assertCount(1, $result->tags);
        $this->assertCount(1, $result->translations);
    }

    /**
     * Test create translation key.
     */
    public function test_create_translation_key(): void
    {
        $result = $this->repository->create(
            'auth.login.title',
            [
                'en' => 'Login',
                'fr' => 'Connexion',
            ],
            ['auth', 'web']
        );

        $this->assertEquals('auth.login.title', $result->key_name);
        $this->assertCount(2, $result->tags);
        $this->assertCount(2, $result->translations);

        $this->assertDatabaseHas('translation_keys', [
            'key_name' => 'auth.login.title',
        ]);

        $this->assertDatabaseHas('translations', [
            'value' => 'Login',
        ]);

        $this->assertDatabaseHas('translations', [
            'value' => 'Connexion',
        ]);
    }

    /**
     * Test update translation key.
     */
    public function test_update_translation_key(): void
    {
        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        $result = $this->repository->update($translationKey->id, [
            'key_name' => 'auth.login.updated',
            'values' => [
                'en' => 'Updated Login',
            ],
            'tags' => ['auth', 'updated'],
        ]);

        $this->assertEquals('auth.login.updated', $result->key_name);
        $this->assertCount(2, $result->tags);

        $this->assertDatabaseHas('translation_keys', [
            'key_name' => 'auth.login.updated',
        ]);
    }

    /**
     * Test export for locale.
     */
    public function test_export_for_locale(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        Translation::factory()->create([
            'translation_key_id' => $translationKey->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);

        $result = $this->repository->exportForLocale([
            'locale' => 'en',
        ]);

        $this->assertArrayHasKey('auth.login.title', $result);
        $this->assertEquals('Login', $result['auth.login.title']);
    }

    /**
     * Test export for locale with tag filtering.
     */
    public function test_export_for_locale_with_tag_filtering(): void
    {
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

        $result = $this->repository->exportForLocale([
            'locale' => 'en',
            'tags' => 'web',
        ]);

        $this->assertArrayHasKey('web.header.title', $result);
        $this->assertEquals('Welcome', $result['web.header.title']);
    }

    /**
     * Test export for locale with multiple tags.
     */
    public function test_export_for_locale_with_multiple_tags(): void
    {
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

        $result = $this->repository->exportForLocale([
            'locale' => 'en',
            'tags' => 'web,auth',
        ]);

        $this->assertArrayHasKey('auth.login.button', $result);
        $this->assertEquals('Sign In', $result['auth.login.button']);
    }

    /**
     * Test export for non-existent locale.
     */
    public function test_export_for_nonexistent_locale(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->exportForLocale([
            'locale' => 'nonexistent',
        ]);
    }

    /**
     * Test stream export for locale.
     */
    public function test_stream_export_for_locale(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKeys = TranslationKey::factory()->count(5)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $generator = $this->repository->streamExportForLocale([
            'locale' => 'en',
        ]);

        $count = 0;
        foreach ($generator as $key => $value) {
            $count++;
            $this->assertIsString($key);
            $this->assertIsString($value);
        }

        $this->assertEquals(5, $count);
    }

    /**
     * Test export performance with small dataset.
     */
    public function test_export_performance_small_dataset(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKeys = TranslationKey::factory()->count(10)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $startTime = microtime(true);
        $result = $this->repository->exportForLocale([
            'locale' => 'en',
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $responseTime, 'Export performance exceeded 100ms for small dataset');
        $this->assertCount(10, $result);
    }

    /**
     * Test export performance with medium dataset.
     */
    public function test_export_performance_medium_dataset(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKeys = TranslationKey::factory()->count(1000)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $startTime = microtime(true);
        $result = $this->repository->exportForLocale([
            'locale' => 'en',
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $responseTime, 'Export performance exceeded 500ms for medium dataset');
        $this->assertCount(1000, $result);
    }

    /**
     * Test export performance with tag filtering.
     */
    public function test_export_performance_with_tag_filtering(): void
    {
        $locale = Locale::factory()->english()->create();
        $tag = Tag::factory()->web()->create();
        $translationKeys = TranslationKey::factory()->count(500)->create();

        foreach ($translationKeys as $key) {
            $key->tags()->attach($tag->id);
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $startTime = microtime(true);
        $result = $this->repository->exportForLocale([
            'locale' => 'en',
            'tags' => 'web',
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $responseTime, 'Export with tag filtering exceeded 500ms');
        $this->assertCount(500, $result);
    }

    /**
     * Test search performance.
     */
    public function test_search_performance(): void
    {
        $locale = Locale::factory()->english()->create();
        $translationKeys = TranslationKey::factory()->count(100)->create();

        foreach ($translationKeys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => "Translation for {$key->key_name}",
            ]);
        }

        $startTime = microtime(true);
        $result = $this->repository->search([
            'locale' => 'en',
        ], 20);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(300, $responseTime, 'Search performance exceeded 300ms');
        $this->assertCount(20, $result);
    }

    /**
     * Test create performance.
     */
    public function test_create_performance(): void
    {
        $startTime = microtime(true);
        $result = $this->repository->create(
            'auth.login.title',
            [
                'en' => 'Login',
                'fr' => 'Connexion',
                'es' => 'Iniciar sesión',
            ],
            ['auth', 'web', 'ui']
        );
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Create performance exceeded 200ms');
        $this->assertEquals('auth.login.title', $result->key_name);
    }

    /**
     * Test update performance.
     */
    public function test_update_performance(): void
    {
        $translationKey = TranslationKey::factory()->create([
            'key_name' => 'auth.login.title',
        ]);

        $startTime = microtime(true);
        $result = $this->repository->update($translationKey->id, [
            'key_name' => 'auth.login.updated',
            'values' => [
                'en' => 'Updated Login',
                'fr' => 'Connexion Mise à jour',
            ],
            'tags' => ['auth', 'updated'],
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Update performance exceeded 200ms');
        $this->assertEquals('auth.login.updated', $result->key_name);
    }
}
