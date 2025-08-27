<?php

namespace App\Http\Controllers\Api\V1;

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
class ExportController extends BaseController
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
     * For large datasets, use the stream parameter to avoid memory issues.
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
     *
     * GET /api/export/en?tags=web,auth&stream=true
     * Response: Streamed JSON response for large datasets
     */
    public function export(string $locale, Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $filters = $this->buildFilters($locale, $validated);

        // Use streaming for large datasets if requested
        if (! empty($validated['stream'])) {
            return $this->streamExport($filters);
        }

        $translations = $this->translationRepository->exportForLocale($filters);

        return response()->json($translations);
    }

    /**
     * Stream export for large datasets.
     */
    private function streamExport(array $filters): JsonResponse
    {
        $response = response()->stream(function () use ($filters) {
            echo '{';
            $first = true;

            foreach ($this->translationRepository->streamExportForLocale($filters) as $key => $value) {
                if (! $first) {
                    echo ',';
                }
                echo '"'.addslashes($key).'":"'.addslashes($value).'"';
                $first = false;
            }

            echo '}';
        }, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ]);

        return $response;
    }

    /**
     * Validate the incoming request.
     *
     *
     * @throws ValidationException
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'tags' => ['sometimes', 'string'], // comma-separated
            'stream' => ['sometimes', 'boolean'], // enable streaming for large datasets
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
