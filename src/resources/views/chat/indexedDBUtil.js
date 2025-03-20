// indexedDBUtil.js

// サーバーからデータを取得してIndexedDBに同期する関数
export function syncData() {
    // エンドポイントを正しく設定（例: /api/chat/data）
    fetch('/api/chat/data')
        .then(response => {
            if (!response.ok) {
                throw new Error('ネットワークエラーが発生しました');
            }
            return response.json();
        })
        .then(data => {
            const request = indexedDB.open("KnowledgeDB", 1);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                // 必要なオブジェクトストアを作成
                if (!db.objectStoreNames.contains("tags")) {
                    db.createObjectStore("tags", { keyPath: "id" });
                }
                if (!db.objectStoreNames.contains("categories")) {
                    db.createObjectStore("categories", { keyPath: "id" });
                }
                if (!db.objectStoreNames.contains("knowledges")) {
                    db.createObjectStore("knowledges", { keyPath: "id" });
                }
                if (!db.objectStoreNames.contains("knowledgeTags")) {
                    db.createObjectStore("knowledgeTags", { keyPath: "id" });
                }
                if (!db.objectStoreNames.contains("knowledgeCategories")) {
                    db.createObjectStore("knowledgeCategories", { keyPath: "id" });
                }
            };

            request.onsuccess = (event) => {
                const db = event.target.result;
                const transaction = db.transaction(
                    ["tags", "categories", "knowledges", "knowledgeTags", "knowledgeCategories"],
                    "readwrite"
                );

                // 各ストアにデータを格納（存在する場合は更新）
                const tagsStore = transaction.objectStore("tags");
                data.tags.forEach(item => tagsStore.put(item));

                const categoriesStore = transaction.objectStore("categories");
                data.categories.forEach(item => categoriesStore.put(item));

                const knowledgesStore = transaction.objectStore("knowledges");
                data.knowledges.forEach(item => knowledgesStore.put(item));

                const knowledgeTagsStore = transaction.objectStore("knowledgeTags");
                data.knowledgeTags.forEach(item => knowledgeTagsStore.put(item));

                const knowledgeCategoriesStore = transaction.objectStore("knowledgeCategories");
                data.knowledgeCategories.forEach(item => knowledgeCategoriesStore.put(item));

                transaction.oncomplete = () => {
                    console.log("最新のデータがIndexedDBに同期されました。");
                    // 同期完了後、IndexedDBの内容を表示する
                    displayDBContents();
                };

                transaction.onerror = (event) => {
                    console.error("トランザクションエラー：", event.target.error);
                };
            };

            request.onerror = (event) => {
                console.error("IndexedDBのオープンに失敗しました：", event.target.error);
            };
        })
        .catch(error => {
            console.error("データ取得エラー：", error);
        });
}

// IndexedDB内の各オブジェクトストアの内容を取得し、HTML上に出力する関数
export function displayDBContents() {
    const container = document.getElementById("db-content");
    container.innerHTML = ""; // 事前に内容をクリア

    const request = indexedDB.open("KnowledgeDB", 1);
    request.onsuccess = (event) => {
        const db = event.target.result;
        const objectStores = ["tags", "categories", "knowledges", "knowledgeTags", "knowledgeCategories"];

        objectStores.forEach(storeName => {
            const transaction = db.transaction(storeName, "readonly");
            const store = transaction.objectStore(storeName);
            const getAllRequest = store.getAll();

            getAllRequest.onsuccess = (event) => {
                const data = event.target.result;
                // ストアごとのデータを整形して表示するための要素を作成
                const storeElement = document.createElement("div");
                storeElement.innerHTML = `<h2>${storeName}</h2><pre>${JSON.stringify(data, null, 2)}</pre>`;
                container.appendChild(storeElement);
            };

            getAllRequest.onerror = (event) => {
                console.error(`Store "${storeName}" のデータ取得エラー：`, event.target.error);
            };
        });
    };

    request.onerror = (event) => {
        console.error("IndexedDBのオープンに失敗しました：", event.target.error);
    };
}

// ページ読み込み時にデータ同期を実行
syncData();
