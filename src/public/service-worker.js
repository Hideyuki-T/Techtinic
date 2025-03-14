import { openDB } from '/js/idb.min.js';

const CACHE_NAME = 'techtinic-cache-v1';
const urlsToCache = [
    '/',
    '/chat',
    '/teach',
    '/knowledge',
    '/css/style.css',
    '/manifest.json',
    '/js/sync.js',
    '/js/idb.min.js',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
    '/favicon.ico',
    '/offline.html',
];

// タイムアウト付き fetch 関数（デフォルトは5000ms）
function fetchWithTimeout(request, timeout = 5000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            reject(new Error('Request timed out'));
        }, timeout);
        fetch(request)
            .then(response => {
                clearTimeout(timer);
                resolve(response);
            })
            .catch(error => {
                clearTimeout(timer);
                reject(error);
            });
    });
}

// インストール時にキャッシュを作成
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
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

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

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
                    // タイムアウト付き fetch を利用
                    return await fetchWithTimeout(event.request, 5000);
                } catch (error) {
                    console.error('通常の fetch でエラー発生:', error);
                    return await handleOfflineChatWithBody(reqBodyText);
                }
            })()
        );
        return;
    }

    event.respondWith(
        fetchWithTimeout(event.request, 5000)
            .then((networkResponse) => {
                // ネットワークから取得できたのでキャッシュを更新
                const responseClone = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseClone);
                });
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request)
                    .then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        if (event.request.mode === 'navigate') {
                            return caches.match('/offline.html');
                        }
                        return new Response(null, { status: 503, statusText: 'Service Unavailable' });
                    });
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

        // IndexedDBへ接続 (openDB を直接呼び出す)
        let db;
        try {
            db = await openDB('techtinic-db', 1, {
                upgrade(db) {
                    if (!db.objectStoreNames.contains('knowledge')) {
                        db.createObjectStore('knowledge', { keyPath: 'id', autoIncrement: true });
                    }
                }
            });
        } catch (e) {
            console.error("IndexedDB接続エラー:", e);
            return new Response(JSON.stringify({
                response: "オフラインなので応答できませんでした。（DB接続エラー）",
                mode: 'default',
                offline: true
            }), { headers: { 'Content-Type': 'application/json' } });
        }

        // DBから 'knowledge' ストアの全レコードを取得
        let allRecords = [];
        try {
            const tx = db.transaction('knowledge', 'readonly');
            const store = tx.objectStore('knowledge');
            allRecords = await store.getAll();
        } catch (e) {
            console.error("データ取得エラー:", e);
            return new Response(JSON.stringify({
                response: "オフラインなので応答できませんでした。（DB取得エラー）",
                mode: 'default',
                offline: true
            }), { headers: { 'Content-Type': 'application/json' } });
        }

        // ユーザー入力に基づいて候補をフィルタリング
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
            responseData = {
                response: `確か...「${matched[0].title}」の内容はこうだったよ!\n${matched[0].content}`,
                mode: 'default',
                offline: true
            };
        } else {
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
