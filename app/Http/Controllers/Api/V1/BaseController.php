<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base Controller for API V1
 *
 * Provides common functionality and version information for all V1 API controllers.
 */
abstract class BaseController extends Controller
{
    /**
     * API Version
     */
    protected const API_VERSION = '1.0.0';

    /**
     * Success response with data
     *
     * @param  mixed  $data
     */
    protected function successResponse($data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'version' => self::API_VERSION,
        ], $statusCode);
    }

    /**
     * Error response
     *
     * @param  mixed  $errors
     */
    protected function errorResponse(string $message, int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'version' => self::API_VERSION,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Paginated response
     *
     * @param  mixed  $data
     */
    protected function paginatedResponse($data, string $message = 'Success'): JsonResponse
    {
        $response = $data->toArray();
        $response['success'] = true;
        $response['message'] = $message;
        $response['version'] = self::API_VERSION;

        return response()->json($response);
    }
}
