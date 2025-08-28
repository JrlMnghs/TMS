<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Exceptions\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Inputs never flashed to session on validation errors.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register callbacks for exception handling.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Already logged by Laravel; you may push to external log services here (e.g., Sentry, Bugsnag).
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Central API exception handler.
     */
    protected function handleApiException(Throwable $e, $request)
    {
        $status = 500;
        $error = 'Server Error';
        $details = null;

        switch (true) {
            case $e instanceof ValidationException:
                $status = 422;
                $error = 'Validation Error';
                $details = $e->errors();
                break;

            case $e instanceof AuthenticationException:
                $status = 401;
                $error = 'Unauthenticated';
                break;

            case $e instanceof AuthorizationException:
            case $e instanceof AccessDeniedHttpException:
                $status = 403;
                $error = 'Forbidden';
                break;

            case $e instanceof ModelNotFoundException:
                $status = 404;
                $error = 'Resource Not Found';
                break;

            case $e instanceof NotFoundHttpException:
            case $e instanceof RouteNotFoundException:
                $status = 404;
                $error = 'Endpoint Not Found';
                break;

            case $e instanceof MethodNotAllowedHttpException:
                $status = 405;
                $error = 'Method Not Allowed';
                break;

            case $e instanceof HttpExceptionInterface:
                $status = $e->getStatusCode();
                $error = $e->getMessage() ?: 'HTTP Error';
                break;
        }

        // Logging (redact sensitive info in production)
        if (app()->environment('local', 'staging')) {
            \Log::error('API Exception', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'type' => class_basename($e),
                'message' => $error,
                'details' => $details,
            ],
        ], $status);
    }
}
