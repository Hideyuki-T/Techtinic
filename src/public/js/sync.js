console.log("sync.js loaded");
console.log("window.idb:", window.idb);

// IP アドレス取得用の関数（Laravel の /api/config から取得）
async function getSyncServerIP() {
    try {
        let response = await fetch("/api/config");
        let data = await response.json();
        return data.sync_server_ip;
    } catch (error) {
        console.error("IPアドレス取得失敗:", error);
        return "localhost";  // デフォルト値
    }
}

// ページ読み込み時に同期を実行し、IndexedDB のデータも UI に表示する
document.addEventListener("DOMContentLoaded", async () => {
    await syncDataFromPC();
    await displayKnowledgeData();
});

// PC側のデータ同期関数
async function syncDataFromPC() {
    const ip = await getSyncServerIP();
    console.log("取得したサーバーIP:", ip);
    fetch(`http://${ip}:8080/api/sync`)
        .then(response => response.json())
        .then(data => {
            console.log("同期データ取得:", data);
            saveKnowledgeData(data);
        })
        .catch(error => console.error("同期失敗:", error));
}

// IndexedDBの初期化関数
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

// UIに IndexedDB のデータを反映する関数
async function displayKnowledgeData() {
    try {
        const data = await getKnowledgeData();
        const listDiv = document.getElementById('knowledge-list');
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
    } catch (error) {
        console.error("知識データの表示に失敗しました:", error);
    }
}

// グローバルに関数を公開する
window.initDB = initDB;
window.saveKnowledgeData = saveKnowledgeData;
window.getKnowledgeData = getKnowledgeData;
window.syncDataFromPC = syncDataFromPC;
