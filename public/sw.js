/**
 * Fer6origami Service Worker
 * Client-Side Caching for Static Assets, Anime Images, and Network-First Navigations
 */

const CACHE_VERSION = 'fer6origami-v2';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const IMAGES_CACHE = `${CACHE_VERSION}-images`;
const PAGES_CACHE = `${CACHE_VERSION}-pages`;
const CURRENT_CACHES = [STATIC_CACHE, IMAGES_CACHE, PAGES_CACHE];

const MAX_IMAGE_ENTRIES = 300;
const MAX_PAGE_ENTRIES = 50;

// Core static assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/',
    '/favicon.ico',
];

// Helper: Trim cache to limit storage usage (LRU-style)
async function trimCache(cacheName, maxItems) {
    try {
        const cache = await caches.open(cacheName);
        const keys = await cache.keys();
        if (keys.length > maxItems) {
            const countToDelete = keys.length - maxItems;
            for (let i = 0; i < countToDelete; i++) {
                await cache.delete(keys[i]);
            }
        }
    } catch (e) {
        // Ignore cache trim errors
    }
}

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch(() => {});
        }).then(() => self.skipWaiting())
    );
});

// Activate Event - clean up obsolete caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (!CURRENT_CACHES.includes(key)) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Listen for messages from client (e.g., clear page cache on login/logout)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CLEAR_PAGES_CACHE') {
        caches.delete(PAGES_CACHE);
    }
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Skip non-http(s) schemes like chrome-extension://
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Skip admin & auth endpoints to avoid stale user state
    const pathname = url.pathname;
    if (
        pathname.startsWith('/admin') ||
        pathname.startsWith('/login') ||
        pathname.startsWith('/register') ||
        pathname.startsWith('/logout') ||
        pathname === '/watchlist' ||
        url.searchParams.has('_token')
    ) {
        return;
    }

    // 1. ANIME IMAGES & PROXIED IMAGES (Cache-First)
    const isImage =
        request.destination === 'image' ||
        pathname.startsWith('/image-proxy') ||
        /\.(png|jpg|jpeg|webp|avif|gif|svg|ico)(\?.*)?$/i.test(pathname) ||
        url.hostname.includes('otakudesu') ||
        url.hostname.includes('candid.asia') ||
        url.hostname.includes('wp.com') ||
        url.hostname.includes('placehold.co');

    if (isImage) {
        event.respondWith(
            caches.open(IMAGES_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                if (cached) {
                    return cached;
                }

                try {
                    const response = await fetch(request);
                    if (response && (response.status === 200 || response.type === 'opaque')) {
                        cache.put(request, response.clone());
                        trimCache(IMAGES_CACHE, MAX_IMAGE_ENTRIES);
                    }
                    return response;
                } catch (err) {
                    return cached || new Response('', { status: 408, statusText: 'Image Request Timeout' });
                }
            })
        );
        return;
    }

    // 2. STATIC ASSETS: Vite build files, Fonts, CSS, JS (Cache-First with Background Update)
    const isStaticAsset =
        pathname.startsWith('/build/') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        /\.(woff2?|ttf|eot|css|js)(\?.*)?$/i.test(pathname);

    if (isStaticAsset) {
        event.respondWith(
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        cache.put(request, networkResponse.clone());
                    }
                    return networkResponse;
                }).catch(() => cached);

                return cached || fetchPromise;
            })
        );
        return;
    }

    // 3. HTML NAVIGATION / PAGES (Network-First with Cache Fallback for 100% fresh content)
    const isNavigation = request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html');

    if (isNavigation) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(PAGES_CACHE).then((cache) => {
                            cache.put(request, copy);
                            trimCache(PAGES_CACHE, MAX_PAGE_ENTRIES);
                        });
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // If network fails (offline), serve from cache
                    return caches.match(request);
                })
        );
    }
});
