console.log("sync.js loaded");
console.log("window.idb:", window.idb);

// タイムアウト用のヘルパー関数（例: 5秒）
function fetchWithTimeout(resource, options = {}) {
    const { timeout = 5000 } = options;
    return Promise.race([
        fetch(resource, options),
        new Promise((_, reject) =>
            setTimeout(() => reject(new Error('タイムアウト')), timeout)
        )
    ]);
}

// IP アドレス取得用の関数（Laravel の /api/config から取得）
async function getSyncServerIP() {
    try {
        let response = await fetchWithTimeout("/api/config", { timeout: 5000 });
        let data = await response.json();
        return data.sync_server_ip;
    } catch (error) {
        console.error("IPアドレス取得失敗:", error);
        return "localhost";  // デフォルト値
    }
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
    const ip = await getSyncServerIP();
    console.log("取得したサーバーIP:", ip);
    fetchWithTimeout(`http://${ip}:8080/api/sync`, { timeout: 5000 })
        .then(response => {
            // レスポンスが JSON 形式でない場合はエラーとして処理
            return response.headers.get('Content-Type')?.includes('application/json')
                ? response.json()
                : Promise.reject(new Error("JSON 形式ではありません"));
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
