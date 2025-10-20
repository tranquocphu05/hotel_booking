<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only optimize successful responses
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Add cache control headers for static assets
        if ($this->isStaticAsset($request)) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }

        // Add compression headers if supported
        if ($this->supportsCompression($request)) {
            $acceptEncoding = $request->header('Accept-Encoding', '');
            
            if (str_contains($acceptEncoding, 'br')) {
                $response->header('Content-Encoding', 'br');
            } elseif (str_contains($acceptEncoding, 'gzip')) {
                $response->header('Content-Encoding', 'gzip');
            }
        }

        // Add security and performance headers
        $response->headers->add([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        ]);

        // Enable HTTP/2 Server Push hints for critical resources
        if ($this->shouldPushResources($request)) {
            $response->header('Link', $this->getPushHeaders());
        }

        return $response;
    }

    /**
     * Check if request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        
        return preg_match('/\.(css|js|jpg|jpeg|png|gif|webp|svg|woff|woff2|ttf|eot|ico)$/i', $path);
    }

    /**
     * Check if client supports compression
     */
    private function supportsCompression(Request $request): bool
    {
        return $request->hasHeader('Accept-Encoding');
    }

    /**
     * Check if should push resources
     */
    private function shouldPushResources(Request $request): bool
    {
        return $request->isMethod('GET') && 
               !$this->isStaticAsset($request) &&
               $request->header('X-Requested-With') !== 'XMLHttpRequest';
    }

    /**
     * Get HTTP/2 Server Push headers
     */
    private function getPushHeaders(): string
    {
        $assets = config('performance.preload_assets', []);
        $headers = [];

        foreach ($assets['css'] ?? [] as $css) {
            $headers[] = "<{$css}>; rel=preload; as=style";
        }

        foreach ($assets['js'] ?? [] as $js) {
            $headers[] = "<{$js}>; rel=preload; as=script";
        }

        foreach ($assets['fonts'] ?? [] as $font) {
            $headers[] = "<{$font}>; rel=preload; as=font; crossorigin";
        }

        return implode(', ', $headers);
    }
}

