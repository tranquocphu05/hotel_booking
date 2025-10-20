<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for performance optimization features
    |
    */

    // Enable/disable asset minification
    'minify_assets' => env('MINIFY_ASSETS', true),

    // Enable/disable asset compression
    'compress_assets' => env('COMPRESS_ASSETS', true),

    // Cache duration in seconds
    'cache_duration' => env('CACHE_DURATION', 31536000), // 1 year

    // Enable/disable lazy loading
    'lazy_loading' => env('LAZY_LOADING', true),

    // Image optimization quality (1-100)
    'image_quality' => env('IMAGE_QUALITY', 85),

    // Enable WebP conversion
    'webp_conversion' => env('WEBP_CONVERSION', true),

    // Service Worker cache version
    'sw_version' => env('SW_VERSION', 'v1.0.0'),

    // Assets to preload
    'preload_assets' => [
        'fonts' => [
            // Add critical fonts here
        ],
        'css' => [
            '/build/assets/app.css',
        ],
        'js' => [
            '/build/assets/app.js',
        ],
    ],

    // CDN configuration
    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL', ''),
    ],
];

