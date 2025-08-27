<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TranslationRepository;
use App\Http\Requests\TranslationIndexRequest;
use App\Http\Requests\TranslationShowRequest;
use App\Http\Requests\TranslationStoreRequest;
use App\Http\Requests\TranslationUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * Class TranslationController
 *
 * Exposes endpoints to create, update, view, and search translations by tags,
 * keys, or content. Responses return current values suitable for frontend apps.
 */
class TranslationController extends Controller
{
    /**
     * Inject translation repository.
     */
    public function __construct(private readonly TranslationRepository $translations)
    {
    }

    /**
     * List/search translations by optional tags, key prefix, content substring, and locale.
     *
     * Query params: tags (csv), key, q, locale, per_page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(TranslationIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $filters = $validated;
        if (!empty($validated['tag']) && empty($validated['tags'])) {
            $filters['tags'] = $validated['tag'];
        }

        $perPage = (int) ($validated['per_page'] ?? 20);
        $results = $this->translations->search($filters, $perPage);

        return response()->json($results);
    }

    /**
     * Show a single translation key with tags and its translations.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, TranslationShowRequest $request): JsonResponse
    {
        $key = $this->translations->findKeyWithRelations($id);

        return response()->json($key);
    }

    /**
     * Create a new translation key with initial localized values and tags.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(TranslationStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $created = $this->translations->create(
            keyName: $data['key_name'],
            valuesByLocale: $data['values'],
            tags: $data['tags'] ?? []
        );
        return response()->json($created, 201);
    }

    /**
     * Update a translation key, tags, and/or localized values.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, TranslationUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $updated = $this->translations->update($id, $data);
        return response()->json($updated);
    }

    /**
     * Delete a translation key and its translations.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = \App\Models\TranslationKey::whereKey($id)->delete();
        return response()->json(['deleted' => (bool) $deleted]);
    }
}
