const CACHE_NAME = 'techtinic-cache-v1';
const urlsToCache = [
    '/',
    '/chat',
    '/css/style.css',
    // 必要なリソースを追加（画像やスクリプトなど）
];

// インストール時にキャッシュを作成
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// リクエスト時にキャッシュを返す（キャッシュヒットの場合）
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // キャッシュにヒットした場合はそれを返す
                if (response) {
                    return response;
                }
                // ヒットしない場合はネットワークから取得
                return fetch(event.request);
            })
    );
});

// アクティベート時に古いキャッシュを削除する
self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
