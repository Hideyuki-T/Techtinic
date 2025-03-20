const CACHE_NAME = 'CSTE-cache-v1';
const urlsToCache = [
    '/',                        // トップページ
    '/main',                    // メインページ
    '/chat',                    // チャットページ
    '/chat/indexedDBUtil.js',   // チャット用JS
    '/chat-data-view',          // チャットデータ表示ページ
    '/game',                    // ゲームトップページ
    '/tetris',                  // テトリスページ
    '/ec',                      // ECサイトページ
    '/url',                     // お気に入り用ページ
    '/css/mainPageStyles.css',  // CSS
    '/quotes.json',             // 名言リストJSON
    '/images/icon_192x192.png', // アイコン
    '/images/icon_512x512.png', // アイコン
    '/api/chat/messages',       // API：チャットメッセージ取得（GET）
    '/api/chat/data'            // API：チャットデータ取得（GET）
];

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

    // API エンドポイント "/api/chat/data" のキャッシュ戦略（ネットワーク優先＆成功したレスポンスをキャッシュ）
    if (requestURL.pathname === '/api/chat/data') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // レスポンスを複製してキャッシュに保存
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    // ネットワークエラーの場合、キャッシュを確認
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
            return response || fetch(event.request);
        })
    );
});
