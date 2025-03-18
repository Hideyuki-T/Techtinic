const CACHE_NAME = 'CSTE-cache-v1';
const urlsToCache = [
    '/',
    '/main',
    '/css/mainPageStyles.css'
    // 必要に応じて他のリソースも追加
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
    );
});
