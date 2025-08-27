<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TranslationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Export Controller
 *
 * Handles export operations for translation data in various formats.
 * Follows SOLID principles and uses repository pattern for data access.
 */
class ExportController extends Controller
{
    private TranslationRepository $translationRepository;

    /**
     * ExportController constructor.
     */
    public function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    /**
     * Export translations for a specific locale.
     *
     * Exports translation keys and their values for the specified locale,
     * optionally filtered by tags. Returns a JSON response with key-value pairs.
     *
     * @param  string  $locale  The locale code (e.g., 'en', 'fr')
     * @param  Request  $request  The HTTP request containing optional filters
     * @return JsonResponse JSON response containing key-value pairs of translations
     *
     * @throws ValidationException When validation fails
     *
     * @example
     * GET /api/export/en?tags=web,auth
     * Response: {"auth.login.title": "Login", "auth.logout.button": "Logout"}
     */
    public function export(string $locale, Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $filters = $this->buildFilters($locale, $validated);

        $translations = $this->translationRepository->exportForLocale($filters);

        return response()->json($translations);
    }

    /**
     * Validate the incoming request.
     *
     *
     *
     * @throws ValidationException
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'tags' => ['sometimes', 'string'], // comma-separated
        ]);
    }

    /**
     * Build filters for the export operation.
     */
    private function buildFilters(string $locale, array $validated): array
    {
        $filters = ['locale' => $locale];

        if (! empty($validated['tags'])) {
            $filters['tags'] = $validated['tags'];
        }

        return $filters;
    }
}
