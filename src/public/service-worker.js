const CACHE_NAME = 'CSTE-cache-v1';
const urlsToCache = [
    '/',                        // トップページ
    '/main',                    // メインページ
    '/chat',                    // チャットページ
    '/game',                    // ゲームトップページ
    '/ec',                      // ECサイトページ
    '/url',                     // お気に入り用ページ
    '/css/mainPageStyles.css',  // CSS
    '/quotes.json',             // 名言リストJSON
    '/icons/apple-icon-180x180.png', // アイコン
    '/images/icon_192x192.png', // アイコン
    '/images/icon_512x512.png', // アイコン
    '/tetris',                  // テトリスページ
    '/js/tetris/main.js',
    '/js/tetris/score.js',
    '/js/tetris/piece.js',
    '/js/tetris/game.js',
    '/js/tetris/renderer.js',
    '/js/tetris/board.js',
    '/css/tetrisPageStyles.css',
];

// タイムアウト付きfetch関数（デフォルトは5000ミリ秒＝5秒）
function fetchWithTimeout(request, timeout = 5000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            reject(new Error('timeout'));
        }, timeout);
        fetch(request)
            .then(response => {
                clearTimeout(timer);
                resolve(response);
            })
            .catch(err => {
                clearTimeout(timer);
                reject(err);
            });
    });
}

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('キャッシュをオープンしました:', CACHE_NAME);
            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener('fetch', event => {
    const requestURL = new URL(event.request.url);

    // API エンドポイント "/api/chat/data" のキャッシュ戦略（ネットワーク優先＆タイムアウト付き）
    if (requestURL.pathname === '/api/chat/data') {
        event.respondWith(
            fetchWithTimeout(event.request, 5000)
                .then(response => {
                    // レスポンスを複製してキャッシュに保存
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    // タイムアウトやネットワークエラーの場合、キャッシュを確認
                    return caches.match(event.request).then(cachedResponse => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // キャッシュがなければ、フォールバックとして空のJSONを返す
                        return new Response(JSON.stringify({
                            tags: [],
                            categories: [],
                            knowledges: [],
                            knowledgeTags: [],
                            knowledgeCategories: []
                        }), {
                            headers: { 'Content-Type': 'application/json' }
                        });
                    });
                })
        );
        return;
    }

    // その他のリクエストはキャッシュ優先
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request).catch(() => {
                // オフライン時のフォールバックページがある場合はそれを返す
                return caches.match('/offline.html');
            });
        })
    );
});
