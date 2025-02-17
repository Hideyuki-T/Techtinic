console.log("sync.js loaded");
console.log("window.idb:", window.idb);

// タイムアウト用のヘルパー関数（タイムアウトを10秒に延長）
function fetchWithTimeout(resource, options = {}) {
    const { timeout = 10000 } = options;  // 10秒に設定
    return Promise.race([
        fetch(resource, options),
        new Promise((_, reject) =>
            setTimeout(() => reject(new Error('タイムアウト')), timeout)
        )
    ]);
}

// IP アドレス取得用の関数（Laravel の /api/config から取得）
// APIから正しい値が得られない場合は、window.location.hostname を使用する
async function getSyncServerIP() {
    try {
        let response = await fetchWithTimeout("/api/config", { timeout: 10000 });
        let data = await response.json();
        if (data.sync_server_ip && data.sync_server_ip !== "undefined") {
            return data.sync_server_ip;
        }
    } catch (error) {
        console.error("IPアドレス取得失敗:", error);
    }
    return window.location.hostname;
}

// DOMContentLoaded 時にオンラインなら同期処理、オフラインならスキップ
document.addEventListener("DOMContentLoaded", async () => {
    if (navigator.onLine) {
        console.log("Online: 同期処理を開始します。");
        await syncDataFromPC();
    } else {
        console.log("Offline: 同期処理はスキップします。");
    }
    await displayKnowledgeData();
});

async function syncDataFromPC() {
    const hostname = window.location.hostname;
    console.log("使用するホスト名:", hostname);
    let syncUrl = `/api/sync`; // 常に相対パスを使用する
    fetchWithTimeout(syncUrl, { timeout: 10000 })
        .then(response => {
            if (!response.headers.get('Content-Type')?.includes('application/json')) {
                return Promise.reject(new Error("JSON 形式ではありません"));
            }
            return response.json();
        })
        .then(data => {
            console.log("同期データ取得:", data);
            saveKnowledgeData(data);
        })
        .catch(error => console.error("同期失敗:", error));
}


// IndexedDB の初期化関数
async function initDB() {
    const db = await window.idb.openDB('techtinic-db', 1, {
        upgrade(db) {
            if (!db.objectStoreNames.contains('knowledge')) {
                const store = db.createObjectStore('knowledge', { keyPath: 'id', autoIncrement: true });
                store.createIndex('title', 'title', { unique: false });
            }
        },
    });
    return db;
}

// データ保存用の関数
async function saveKnowledgeData(data) {
    const db = await initDB();
    const tx = db.transaction('knowledge', 'readwrite');
    const store = tx.objectStore('knowledge');
    await store.clear();
    for (const item of data.knowledge) {
        await store.add(item);
    }
    await tx.done;
    console.log("IndexedDBへの保存が完了しました。");
}

// データ取得用の関数
async function getKnowledgeData() {
    const db = await initDB();
    const allItems = await db.getAll('knowledge');
    return allItems;
}

// UI に IndexedDB のデータを反映する関数
async function displayKnowledgeData() {
    try {
        const data = await getKnowledgeData();
        const listDiv = document.getElementById('knowledge-list');
        if (listDiv) {
            listDiv.innerHTML = '';
            if (data.length === 0) {
                listDiv.innerHTML = '<p>キャッシュされた知識はありません。</p>';
                return;
            }
            data.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'knowledge-item';
                itemDiv.innerHTML = `<strong>${item.title}</strong>: ${item.content}`;
                listDiv.appendChild(itemDiv);
            });
        }
    } catch (error) {
        console.error("知識データの表示に失敗しました:", error);
    }
}

// グローバルに関数を公開する
window.initDB = initDB;
window.saveKnowledgeData = saveKnowledgeData;
window.getKnowledgeData = getKnowledgeData;
window.syncDataFromPC = syncDataFromPC;
