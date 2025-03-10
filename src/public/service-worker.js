importScripts('/js/idb.min.js');

const CACHE_NAME = 'techtinic-cache-v1';
const urlsToCache = [
    '/',                          // ルートページ
    '/chat',                      // チャットページ（必要なら）
    '/teach',                     // 知識登録ページ（必要なら）
    '/knowledge',                 // 知識表示ページ（必要なら）
    '/css/style.css',             // CSS
    '/manifest.json',             // マニフェスト
    '/js/sync.js',                // 同期スクリプト
    '/js/idb.min.js',             // IndexedDBライブラリ
    '/images/icons/icon-192x192.png',  // アイコン
    '/images/icons/icon-512x512.png',  // 大きなアイコン
    '/favicon.ico',               // favicon もキャッシュ対象に追加
    '/offline.html',              // オフライン用フォールバックページ
];

// インストール時にキャッシュを作成
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
                // 個別に fetch してキャッシュに保存（個別エラーはログ出力）
                return Promise.all(
                    urlsToCache.map(url => {
                        return fetch(url).then(response => {
                            if (!response.ok) {
                                console.error(`Failed to fetch ${url}: ${response.statusText}`);
                            }
                            return cache.put(url, response);
                        }).catch(error => {
                            console.error(`Error caching ${url}:`, error);
                        });
                    })
                );
            })
    );
});

// fetch イベントでチャット用APIとその他のリクエストを処理
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // チャット用API (/api/chat) の場合
    if (url.pathname.startsWith('/api/chat')) {
        event.respondWith(
            (async function() {
                let reqBodyText = "";
                try {
                    reqBodyText = await event.request.clone().text();
                } catch (e) {
                    console.error("リクエストボディ読み込みエラー:", e);
                }
                try {
                    // オンラインの場合、通常のネットワークリクエストを試みる
                    return await fetch(event.request);
                } catch (error) {
                    console.error('通常の fetch でエラー発生:', error);
                    // ネットワークエラーの場合は offline 用処理へフォールバック
                    return await handleOfflineChatWithBody(reqBodyText);
                }
            })()
        );
        return;
    }

    // その他のリクエストはキャッシュまたはネットワークから取得
    event.respondWith(
        caches.match(event.request)
            .then((response) => response || fetch(event.request))
            .catch((error) => {
                console.error('キャッシュ/ネットワーク取得エラー:', error);
                // オフラインでエラーが発生しても、フォールバックしない（または適宜空のResponseを返す）
                return new Response(null, { status: 503, statusText: 'Service Unavailable' });
            })
    );
});

// オフライン時のチャット応答処理
async function handleOfflineChatWithBody(reqText) {
    console.log("handleOfflineChatWithBody: reqText =", reqText);
    try {
        let reqData = {};
        try {
            reqData = JSON.parse(reqText);
            console.log("Parsed reqData:", reqData);
        } catch (e) {
            console.error("JSONパースエラー:", e);
        }
        const userInput = reqData.message || '';
        // 動的にインポートした openDB を利用
        const db = await idb.openDB('techtinic-db', 1, {
            upgrade(db) {
                if (!db.objectStoreNames.contains('knowledge')) {
                    db.createObjectStore('knowledge', { keyPath: 'id', autoIncrement: true });
                }
            }
        });
        const tx = db.transaction('knowledge', 'readonly');
        const store = tx.objectStore('knowledge');
        const allRecords = await store.getAll();

        // 複数の候補をフィルタリング（タイトルまたは本文にユーザー入力が含まれているもの）
        const matched = allRecords.filter(item =>
            item.title.toLowerCase().includes(userInput.toLowerCase()) ||
            item.content.toLowerCase().includes(userInput.toLowerCase())
        );

        let responseData;
        if (matched.length === 0) {
            responseData = {
                response: "それについてはまだ知らないや。",
                mode: 'default',
                offline: true
            };
        } else if (matched.length === 1) {
            // 候補が1件だけなら、そのまま返す
            responseData = {
                response: `確か...「${matched[0].title}」の内容はこうだったよ!\n${matched[0].content}`,
                mode: 'default',
                offline: true
            };
        } else {
            // 候補が複数ある場合は選択肢として返す
            responseData = {
                response: "以下の情報が見つかったよ。どれか選んで！",
                mode: 'selection',
                options: matched.map(item => item.title),
                offline: true
            };
        }

        return new Response(JSON.stringify(responseData), {
            headers: { 'Content-Type': 'application/json' }
        });
    } catch (error) {
        console.error('handleOfflineChat 内のエラー:', error);
        return new Response(JSON.stringify({
            response: "オフラインなので応答できませんでした。（内部エラー）",
            mode: 'default',
            offline: true
        }), {
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// アクティベート時に古いキャッシュを削除
self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        Promise.all([
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (!cacheWhitelist.includes(cacheName)) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            self.clients.claim()
        ])
    );
});
