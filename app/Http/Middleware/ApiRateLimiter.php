<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        // Different rate limits for different endpoints
        $maxAttempts = $this->getMaxAttempts($request);
        $decayMinutes = $this->getDecayMinutes($request);
        
        if (RateLimiterFacade::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }
        
        RateLimiterFacade::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
    
    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = $request->user();
        $ip = $request->ip();
        
        if ($user) {
            return sha1($user->id . '|' . $ip . '|' . $request->route()?->getName());
        }
        
        return sha1($ip . '|' . $request->route()?->getName());
    }
    
    /**
     * Get the maximum number of attempts for the given request.
     */
    protected function getMaxAttempts(Request $request): int
    {
        $route = $request->route();
        
        // Different limits for different endpoint types
        if (str_contains($route?->getName() ?? '', 'export')) {
            return 30; // Export endpoints: 30 requests per hour
        }
        
        if (str_contains($route?->getName() ?? '', 'login')) {
            return 5; // Login: 5 attempts per hour
        }
        
        if (str_contains($route?->getName() ?? '', 'translations')) {
            return 100; // CRUD operations: 100 requests per hour
        }
        
        if (str_contains($route?->getName() ?? '', 'users')) {
            return 50; // User operations: 50 requests per hour
        }
        
        return 60; // Default: 60 requests per hour
    }
    
    /**
     * Get the number of minutes to decay the rate limiter.
     */
    protected function getDecayMinutes(Request $request): int
    {
        $route = $request->route();
        
        // Different decay times for different endpoints
        if (str_contains($route?->getName() ?? '', 'login')) {
            return 1; // Login: 1 minute
        }
        
        return 60; // Default: 1 hour
    }
    
    /**
     * Create a 'too many attempts' response.
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiterFacade::availableIn($key);
        
        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter,
            'max_attempts' => $maxAttempts,
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => time() + $retryAfter,
        ]);
    }
    
    /**
     * Add the limit header information to the given response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);
    }
    
    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - RateLimiterFacade::attempts($key) + 1;
    }
}
