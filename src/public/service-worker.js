import { openDB } from '/js/idb.min.js';

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
                // フォールバックとして offline.html を返す
                return caches.match('/offline.html');
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
        const db = await openDB('techtinic-db', 1);
        const tx = db.transaction('knowledge', 'readonly');
        const store = tx.objectStore('knowledge');
        const allRecords = await store.getAll();

        // シンプルな検索例: タイトルが完全一致、またはコンテンツに含まれる
        const matched = allRecords.find(item =>
            item.title === userInput || item.content.includes(userInput)
        );

        let responseData;
        if (matched) {
            responseData = {
                response: `選択された知識「${matched.title}」の内容は以下です:\n${matched.content}`,
                mode: 'default',
                offline: true
            };
        } else {
            responseData = {
                response: "申し訳ありません、その知識はまだ教えられていません。",
                mode: 'default',
                offline: true
            };
        }

        return new Response(JSON.stringify(responseData), {
            headers: { 'Content-Type': 'application/json' }
        });
    } catch (error) {
        console.error('handleOfflineChat 内のエラー:', error);
        return new Response(JSON.stringify({
            response: "オフラインでも応答できませんでした。（内部エラー）",
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
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!cacheWhitelist.includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
