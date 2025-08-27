<?php

namespace App\Repositories;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Repository for managing translation keys and their associated data.
 *
 * This repository provides optimized methods for searching, creating, updating,
 * and retrieving translation keys with their related translations, locales, and tags.
 * It uses full-text search capabilities for improved performance on large datasets.
 *
 * @author Translation Management Service
 *
 * @since 1.0.0
 */
class TranslationRepository
{
    /**
     * Search translation keys with various filters using optimized queries.
     *
     * This method performs a high-performance search across translation keys
     * using full-text search capabilities for keyword matching. It supports
     * filtering by keyword, key name, locale, and tags with optimized joins
     * to minimize query execution time.
     *
     * Performance optimizations include:
     * - Full-text search for keyword and key name filtering
     * - Optimized joins to reduce query complexity
     * - Selective column loading to minimize data transfer
     * - Eager loading of relationships only for paginated results
     *
     * @param  array  $filters  Search criteria with the following keys:
     *                          - 'keyword' (string): Search in translation values using full-text search
     *                          - 'key' (string): Filter by translation key name prefix
     *                          - 'locale' (string): Filter by locale code (e.g., 'en', 'fr')
     *                          - 'tags' (string|array): Filter by tag names (comma-separated string or array)
     * @param  int  $perPage  Number of results per page (default: 20)
     * @return LengthAwarePaginator Paginated collection of TranslationKey models with loaded relationships
     *
     * @example
     * // Search for translations containing "login" in English locale
     * $results = $repository->search([
     *     'keyword' => 'login',
     *     'locale' => 'en'
     * ], 10);
     *
     * // Search for keys starting with "auth" and tagged with "web"
     * $results = $repository->search([
     *     'key' => 'auth',
     *     'tags' => 'web'
     * ]);
     *
     * @throws \Illuminate\Database\QueryException When database query fails
     * @throws \InvalidArgumentException When invalid filter values are provided
     */
    public function search(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        // Build base query with optimized joins
        $query = TranslationKey::query()
            ->select([
                'translation_keys.id',
                'translation_keys.key_name',
                'translation_keys.description',
            ])
            ->distinct();

        // Add keyword filter with FULLTEXT search (much faster than LIKE)
        if (! empty($filters['keyword'])) {
            $query->join('translations', 'translation_keys.id', '=', 'translations.translation_key_id')
                ->whereRaw('MATCH(translations.value) AGAINST(? IN BOOLEAN MODE)', [$filters['keyword']]);
        }

        // Add key filter with FULLTEXT search
        if (! empty($filters['key'])) {
            $query->whereRaw('MATCH(translation_keys.key_name) AGAINST(? IN BOOLEAN MODE)', [$filters['key']]);
        }

        // Add locale filter
        if (! empty($filters['locale'])) {
            if (empty($filters['keyword'])) {
                $query->join('translations', 'translation_keys.id', '=', 'translations.translation_key_id');
            }
            $query->join('locales', 'translations.locale_id', '=', 'locales.id')
                ->where('locales.code', $filters['locale']);
        }

        // Add tags filter
        if (! empty($filters['tags'])) {
            $tagList = is_array($filters['tags'])
                ? $filters['tags']
                : array_filter(array_map('trim', explode(',', $filters['tags'])));

            if (! empty($tagList)) {
                $query->join('translation_key_tags', 'translation_keys.id', '=', 'translation_key_tags.translation_key_id')
                    ->join('tags', 'translation_key_tags.tag_id', '=', 'tags.id')
                    ->whereIn('tags.name', $tagList);
            }
        }

        // Get paginated results
        $translationKeys = $query->orderBy('translation_keys.id')
            ->paginate($perPage);

        // Eager load relationships for the paginated results only
        $translationKeys->getCollection()->load([
            'tags:id,name',
            'translations' => function ($q) use ($filters) {
                $q->select(['id', 'translation_key_id', 'locale_id', 'value', 'status'])
                    ->with('locale:id,code,name');

                if (! empty($filters['locale'])) {
                    $q->whereHas('locale', fn ($l) => $l->where('code', $filters['locale']));
                }

                if (! empty($filters['keyword'])) {
                    $q->whereRaw('MATCH(value) AGAINST(? IN BOOLEAN MODE)', [$filters['keyword']]);
                }
            },
        ]);

        return $translationKeys;
    }

    /**
     * Find a translation key by ID with all related data loaded.
     *
     * Retrieves a single translation key and eagerly loads its associated
     * tags and translations with locale information. This method is optimized
     * to load only necessary columns to minimize memory usage.
     *
     * @param  int  $id  The ID of the translation key to retrieve
     * @return TranslationKey The translation key model with loaded relationships
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When translation key is not found
     *
     * @example
     * $translationKey = $repository->findKeyWithRelations(123);
     * echo $translationKey->key_name; // "auth.login.title"
     * echo $translationKey->tags->first()->name; // "auth"
     * echo $translationKey->translations->first()->value; // "Login"
     */
    public function findKeyWithRelations(int $id): TranslationKey
    {
        return TranslationKey::with([
            'tags:id,name',
            'translations.locale:id,code,name',
        ])->findOrFail($id);
    }

    /**
     * Create a new translation key with translations and tags.
     *
     * This method creates a new translation key and its associated translations
     * for multiple locales, along with tag assignments. The entire operation
     * is wrapped in a database transaction to ensure data consistency.
     *
     * @param  string  $keyName  The unique name/identifier for the translation key
     * @param  array  $valuesByLocale  Associative array of locale codes to translation values
     *                                 Format: ['en' => 'English text', 'fr' => 'French text']
     * @param  array  $tags  Array of tag names to associate with the translation key
     * @return TranslationKey The created translation key with loaded relationships
     *
     * @throws \Illuminate\Database\QueryException When database operation fails
     * @throws \InvalidArgumentException When invalid data is provided
     *
     * @example
     * $translationKey = $repository->create(
     *     'auth.login.title',
     *     ['en' => 'Login', 'fr' => 'Connexion'],
     *     ['auth', 'web']
     * );
     */
    public function create(string $keyName, array $valuesByLocale, array $tags = []): TranslationKey
    {
        return DB::transaction(function () use ($keyName, $valuesByLocale, $tags) {
            $tKey = TranslationKey::create([
                'key_name' => $keyName,
                'description' => null,
            ]);

            if (! empty($tags)) {
                $tagIds = collect($tags)->map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id
                )->all();
                $tKey->tags()->sync($tagIds);
            }

            foreach ($valuesByLocale as $localeCode => $value) {
                $locale = Locale::firstOrCreate(
                    ['code' => $localeCode],
                    ['name' => strtoupper($localeCode)]
                );

                Translation::updateOrCreate(
                    [
                        'translation_key_id' => $tKey->id,
                        'locale_id' => $locale->id,
                    ],
                    [
                        'value' => (string) $value,
                        'status' => 'approved',
                    ]
                );
            }

            return $this->findKeyWithRelations($tKey->id);
        });
    }

    /**
     * Update an existing translation key and its associated data.
     *
     * Updates a translation key's properties, translations, and tag associations.
     * Only provided fields are updated, and the entire operation is wrapped in
     * a database transaction for data consistency.
     *
     * @param  int  $id  The ID of the translation key to update
     * @param  array  $payload  Update data with the following optional keys:
     *                          - 'key_name' (string): New name for the translation key
     *                          - 'tags' (array): Array of tag names to replace existing tags
     *                          - 'values' (array): Associative array of locale codes to translation values
     * @return TranslationKey The updated translation key with loaded relationships
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When translation key is not found
     * @throws \Illuminate\Database\QueryException When database operation fails
     *
     * @example
     * $updatedKey = $repository->update(123, [
     *     'key_name' => 'auth.login.button',
     *     'tags' => ['auth', 'ui'],
     *     'values' => ['en' => 'Sign In', 'fr' => 'Se connecter']
     * ]);
     */
    public function update(int $id, array $payload): TranslationKey
    {
        return DB::transaction(function () use ($id, $payload) {
            $tKey = TranslationKey::findOrFail($id);

            if (array_key_exists('key_name', $payload)) {
                $tKey->key_name = $payload['key_name'];
                $tKey->save();
            }

            if (array_key_exists('tags', $payload)) {
                $tagIds = collect($payload['tags'] ?? [])->map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id
                )->all();
                $tKey->tags()->sync($tagIds);
            }

            if (! empty($payload['values'])) {
                foreach ($payload['values'] as $localeCode => $value) {
                    $locale = Locale::firstOrCreate(
                        ['code' => $localeCode],
                        ['name' => strtoupper($localeCode)]
                    );

                    Translation::updateOrCreate(
                        [
                            'translation_key_id' => $tKey->id,
                            'locale_id' => $locale->id,
                        ],
                        [
                            'value' => (string) $value,
                            'status' => 'approved',
                        ]
                    );
                }
            }

            return $this->findKeyWithRelations($tKey->id);
        });
    }

    /**
     * Export translations for a specific locale with optional tag filtering.
     *
     * This method exports translation keys and their values for the specified locale,
     * optionally filtered by tags. It returns an associative array where keys are
     * translation key names and values are the corresponding translations.
     *
     * The method is optimized for performance on large datasets by:
     * - Using model scopes for clean, maintainable queries
     * - Single query execution with proper indexing
     * - Efficient tag filtering with indexed joins
     * - Direct array building for optimal performance
     *
     * @param  array  $filters  Export criteria with the following keys:
     *                          - 'locale' (string): Required locale code (e.g., 'en', 'fr')
     *                          - 'tags' (string): Optional comma-separated tag names for filtering
     * @return array Associative array of translation key names to translation values
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When locale is not found
     * @throws \Illuminate\Database\QueryException When database query fails
     *
     * @example
     * // Export all English translations
     * $translations = $repository->exportForLocale(['locale' => 'en']);
     *
     * // Export English translations tagged with 'web' and 'auth'
     * $translations = $repository->exportForLocale([
     *     'locale' => 'en',
     *     'tags' => 'web,auth'
     * ]);
     *
     * // Result: ['auth.login.title' => 'Login', 'auth.logout.button' => 'Logout']
     */
    public function exportForLocale(array $filters): array
    {
        $tagList = [];
        if (! empty($filters['tags'])) {
            $tagList = array_filter(array_map('trim', explode(',', $filters['tags'])));
        }

        // Use model method for clean, optimized query
        $results = TranslationKey::getExportData($filters['locale'], $tagList);

        // Build result array
        $result = [];
        foreach ($results as $row) {
            $result[$row->key_name] = $row->value ?? '';
        }

        return $result;
    }

    /**
     * Stream export translations for a specific locale with optional tag filtering.
     *
     * This method is designed for very large datasets where loading all results
     * into memory would be problematic. It streams the results in chunks and
     * yields them one by one for memory-efficient processing.
     *
     * @param  array  $filters  Export criteria with the following keys:
     *                          - 'locale' (string): Required locale code (e.g., 'en', 'fr')
     *                          - 'tags' (string): Optional comma-separated tag names for filtering
     *                          - 'chunk_size' (int): Number of records to process per chunk (default: 1000)
     * @return \Generator Yields key-value pairs for translations
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When locale is not found
     * @throws \Illuminate\Database\QueryException When database query fails
     *
     * @example
     * // Stream all English translations
     * foreach ($repository->streamExportForLocale(['locale' => 'en']) as $key => $value) {
     *     echo "$key: $value\n";
     * }
     */
    public function streamExportForLocale(array $filters): \Generator
    {
        $chunkSize = $filters['chunk_size'] ?? 1000;

        $tagList = [];
        if (! empty($filters['tags'])) {
            $tagList = array_filter(array_map('trim', explode(',', $filters['tags'])));
        }

        // Use model method for clean, optimized streaming
        yield from TranslationKey::streamExportData($filters['locale'], $tagList, $chunkSize);
    }
}
