// service-worker.js
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

// fetch イベントの処理
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // /api/chat へのリクエストは IndexedDB を利用してチャット処理を行う
    if (url.pathname.startsWith('/api/chat')) {
        event.respondWith(
            (async function() {
                let reqBodyText = "";
                try {
                    reqBodyText = await event.request.clone().text();
                } catch (e) {
                    console.error("リクエストボディの読み込みでエラー:", e);
                }
                return await handleChatMessage(reqBodyText);
            })()
        );
        return;
    }

    // GET 以外はキャッシュ対象外
    if (event.request.method !== 'GET') {
        event.respondWith(fetch(event.request));
        return;
    }

    event.respondWith(
        fetchWithTimeout(event.request, 5000)
            .then((networkResponse) => {
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

// IndexedDB の初期化（バージョン2に統一）
async function initDB() {
    const db = await openDB('techtinic-db', 2, {
        upgrade(db, oldVersion, newVersion, transaction) {
            if (!db.objectStoreNames.contains('knowledge')) {
                const store = db.createObjectStore('knowledge', { keyPath: 'id', autoIncrement: true });
                store.createIndex('title', 'title', { unique: false });
            }
            if (!db.objectStoreNames.contains('chatMessages')) {
                db.createObjectStore('chatMessages', { keyPath: 'id', autoIncrement: true });
            }
        }
    });
    return db;
}

// チャットリクエストの処理（IndexedDB を利用）
async function handleChatMessage(reqText) {
    console.log("handleChatMessage: reqText =", reqText);
    let reqData = {};
    try {
        reqData = JSON.parse(reqText);
        console.log("Parsed reqData:", reqData);
    } catch (e) {
        console.error("JSONパースエラー:", e);
    }
    const userInput = reqData.message || '';

    let db;
    try {
        db = await initDB();
    } catch (e) {
        console.error("IndexedDB接続エラー:", e);
        return new Response(JSON.stringify({
            response: "IndexedDB 接続エラーです。",
            offline: true
        }), { headers: { 'Content-Type': 'application/json' } });
    }

    try {
        const tx = db.transaction('chatMessages', 'readwrite');
        const store = tx.objectStore('chatMessages');
        const chatMessage = { message: userInput, timestamp: Date.now() };
        await store.add(chatMessage);
        await tx.done;
        console.log("チャットメッセージを IndexedDB に保存しました。", chatMessage);
    } catch (e) {
        console.error("チャットメッセージ保存エラー:", e);
    }

    let allMessages = [];
    try {
        const tx = db.transaction('chatMessages', 'readonly');
        const store = tx.objectStore('chatMessages');
        allMessages = await store.getAll();
    } catch (e) {
        console.error("チャットメッセージ取得エラー:", e);
    }

    const responseData = {
        response: "チャットメッセージ一覧です。",
        messages: allMessages,
        offline: true
    };

    return new Response(JSON.stringify(responseData), {
        headers: { 'Content-Type': 'application/json' }
    });
}

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
