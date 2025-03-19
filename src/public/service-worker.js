const CACHE_NAME = 'CSTE-cache-v1';
const urlsToCache = [
    '/',
    '/main',
    '/chat',
    '/ec',
    '/game',
    '/css/mainPageStyles.css',
    '/quotes.json',
    '/images/icon_192x192.png',
    '/images/icon_512x512.png'
    // 必要に応じて他のリソースも追加
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                // 各リソースごとにキャッシュ追加し、失敗した場合はエラーハンドリング
                return Promise.all(urlsToCache.map(url => {
                    return cache.add(url).catch(error => {
                        console.error('キャッシュに失敗:', url, error);
                    });
                }));
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});
