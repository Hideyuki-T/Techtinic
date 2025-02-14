const DB_NAME = "TechtinicDB";
const DB_VERSION = 1;
let db;

function openDatabase(callback) {
    let request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = function (event) {
        let db = event.target.result;
        if (!db.objectStoreNames.contains("knowledge")) {
            let store = db.createObjectStore("knowledge", { keyPath: "title" });
            store.createIndex("category", "category", { unique: false });
        }
    };

    request.onsuccess = function (event) {
        db = event.target.result;
        if (callback) callback();
    };

    request.onerror = function (event) {
        console.error("IndexedDBのオープンに失敗:", event.target.errorCode);
    };
}

// IndexedDBにデータを保存（トランザクション付き）
function saveDataToIndexedDB(data) {
    if (!db) {
        console.error("IndexedDBが開かれていません");
        return;
    }

    let transaction = db.transaction(["knowledge"], "readwrite");
    let store = transaction.objectStore("knowledge");

    // トランザクション完了時の処理
    transaction.oncomplete = function () {
        console.log("全データ同期成功！");
    };

    // トランザクションエラー時の処理（ロールバック）
    transaction.onerror = function (event) {
        console.error("データ同期中にエラーが発生！", event.target.error);
    };

    // すべてのデータを追加
    try {
        data.knowledge.forEach(item => {
            store.put(item);
        });
    } catch (error) {
        console.error("データの挿入に失敗", error);
        transaction.abort(); // ロールバック
    }
}
