/**
 * Service Worker for Ozia Hotel Booking
 * Caches static assets for faster page loads
 */

const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `ozia-hotel-${CACHE_VERSION}`;

// Assets to cache immediately on install
const PRECACHE_ASSETS = [
    '/',
    '/build/assets/app.css',
    '/build/assets/app.js',
];

// Install event - cache initial assets
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[ServiceWorker] Precaching assets');
                return cache.addAll(PRECACHE_ASSETS.map(url => new Request(url, {credentials: 'same-origin'})));
            })
            .then(() => {
                console.log('[ServiceWorker] Skip waiting');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[ServiceWorker] Precaching failed:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((cacheName) => {
                            // Delete old caches
                            return cacheName.startsWith('ozia-hotel-') && cacheName !== CACHE_NAME;
                        })
                        .map((cacheName) => {
                            console.log('[ServiceWorker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                console.log('[ServiceWorker] Claiming clients');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome extension requests
    if (url.protocol === 'chrome-extension:') {
        return;
    }
    
    // Skip API calls (let them go to network)
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) {
        return;
    }
    
    event.respondWith(
        caches.match(request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version
                    return cachedResponse;
                }
                
                // Clone the request
                const fetchRequest = request.clone();
                
                return fetch(fetchRequest)
                    .then((response) => {
                        // Check if valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        
                        // Clone the response
                        const responseToCache = response.clone();
                        
                        // Cache static assets
                        if (shouldCache(url)) {
                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(request, responseToCache);
                                });
                        }
                        
                        return response;
                    })
                    .catch((error) => {
                        console.error('[ServiceWorker] Fetch failed:', error);
                        
                        // Return offline page if available
                        return caches.match('/offline.html');
                    });
            })
    );
});

/**
 * Check if URL should be cached
 */
function shouldCache(url) {
    const pathname = url.pathname;
    
    // Cache CSS, JS, images, fonts
    const cacheable = [
        /\.css$/,
        /\.js$/,
        /\.jpg$/,
        /\.jpeg$/,
        /\.png$/,
        /\.gif$/,
        /\.webp$/,
        /\.svg$/,
        /\.woff$/,
        /\.woff2$/,
        /\.ttf$/,
        /\.eot$/,
    ];
    
    return cacheable.some(pattern => pattern.test(pathname));
}

// Message event - handle commands from main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAME)
            .then(() => {
                console.log('[ServiceWorker] Cache cleared');
            });
    }
});


