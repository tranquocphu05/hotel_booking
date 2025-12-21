<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Skip caching for authenticated admin/users (they see dynamic content)
        if (auth()->check() && (auth()->user()->vai_tro === 'admin' || auth()->user()->vai_tro === 'le_tan')) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = 'response_' . md5($request->fullUrl() . '_' . ($request->header('Accept-Language') ?? 'vi'));

        // Check if response is cached
        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse !== null) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers']);
        }

        // Get response
        $response = $next($request);

        // Cache successful responses for public pages (5 minutes)
        if ($response->getStatusCode() === 200 && !auth()->check()) {
            $cacheDuration = 300; // 5 minutes
            
            // Longer cache for static pages
            if ($request->routeIs('client.gioi-thieu') || $request->routeIs('client.tin-tuc.*')) {
                $cacheDuration = 1800; // 30 minutes
            }

            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], $cacheDuration);
        }

        return $response;
    }
}

