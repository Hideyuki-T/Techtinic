<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IndexedDB Insert Example</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
    <style>
        form { margin: 1em 0; }
        label { display: block; margin-top: 1em; }
        input, textarea { width: 100%; padding: 0.5em; }
        button { margin-top: 1em; }
        .knowledge-item { border: 1px solid #ccc; padding: 1em; margin-bottom: 1em; }
    </style>
</head>
<body>
<h1>IndexedDB Contents</h1>
<div id="db-content"></div>
<script src="chat-knowledge-db.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", displayDBContents);
</script>
<h1>データ挿入</h1>
<p>カテゴリー、タイトル、本文、タグ の順でデータを登録します</p>

<!-- データ挿入用フォーム -->
<form id="dataForm">
    <label>カテゴリー:
        <input type="text" name="category" required>
    </label>
    <label>タイトル:
        <input type="text" name="title" required>
    </label>
    <label>本文:
        <textarea name="content" required></textarea>
    </label>
    <label>タグ (カンマ区切り):
        <input type="text" name="tags" required>
    </label>
    <button type="submit">データを挿入</button>
</form>

<!-- IndexedDB内の内容表示用 -->
<div id="db-content"></div>

<!-- IndexedDB初期化用スクリプト -->
<script src="chat-knowledge-db.js"></script>

<script>
    // ヘルパー：指定ストアの全データ取得
    function getData(db, storeName) {
        return new Promise((resolve, reject) => {
            const transaction = db.transaction(storeName, "readonly");
            const store = transaction.objectStore(storeName);
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // ヘルパー：ストアのインデックスを使ってレコードを検索
    function findRecordByIndex(store, indexName, value) {
        return new Promise((resolve, reject) => {
            const index = store.index(indexName);
            const req = index.get(value);
            req.onsuccess = e => resolve(e.target.result);
            req.onerror = e => reject(e.target.error);
        });
    }

    // フォーム送信時にデータをIndexedDBへ挿入
    document.getElementById("dataForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const categoryInput = formData.get("category").trim();
        const titleInput = formData.get("title").trim();
        const contentInput = formData.get("content").trim();
        const tagsInput = formData.get("tags").trim();
        // タグはカンマ区切りで複数指定可能
        const tagList = tagsInput.split(",").map(t => t.trim()).filter(t => t);

        window.getChatKnowledgeDB(function(db) {
            // 必要なストア全てで読み書き可能なトランザクション開始
            const transaction = db.transaction(
                ["chat_knowledge", "chat_categories", "chat_tags", "chat_knowledge_category", "chat_knowledge_tag"],
                "readwrite"
            );

            // Step1: chat_knowledge への挿入
            const knowledgeStore = transaction.objectStore("chat_knowledge");
            const knowledgeData = {
                title: titleInput,
                content: contentInput,
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
                deleted_at: null
            };
            const knowledgeRequest = knowledgeStore.add(knowledgeData);
            knowledgeRequest.onsuccess = function(e) {
                const knowledgeId = e.target.result;

                // Step2: chat_categories への挿入（既存チェック）
                const categoriesStore = transaction.objectStore("chat_categories");
                findRecordByIndex(categoriesStore, "name", categoryInput)
                    .then(existingCategory => {
                        if (existingCategory) {
                            return existingCategory.id;
                        } else {
                            return new Promise((resolve, reject) => {
                                const catData = {
                                    name: categoryInput,
                                    created_at: new Date().toISOString(),
                                    updated_at: new Date().toISOString()
                                };
                                const addCatReq = categoriesStore.add(catData);
                                addCatReq.onsuccess = e => resolve(e.target.result);
                                addCatReq.onerror = e => reject(e.target.error);
                            });
                        }
                    })
                    .then(categoryId => {
                        // Step3: chat_knowledge_category に紐付けレコード挿入
                        const knowledgeCategoryStore = transaction.objectStore("chat_knowledge_category");
                        const relationData = {
                            knowledge_id: knowledgeId,
                            category_id: categoryId,
                            created_at: new Date().toISOString(),
                            updated_at: new Date().toISOString()
                        };
                        knowledgeCategoryStore.add(relationData);
                    })
                    .catch(err => console.error("カテゴリーの処理エラー:", err));

                // Step4: 各タグについて処理
                const tagsStore = transaction.objectStore("chat_tags");
                const knowledgeTagStore = transaction.objectStore("chat_knowledge_tag");
                tagList.forEach(tagName => {
                    findRecordByIndex(tagsStore, "name", tagName)
                        .then(existingTag => {
                            if (existingTag) {
                                return existingTag.id;
                            } else {
                                return new Promise((resolve, reject) => {
                                    const tagData = {
                                        name: tagName,
                                        created_at: new Date().toISOString(),
                                        updated_at: new Date().toISOString()
                                    };
                                    const addTagReq = tagsStore.add(tagData);
                                    addTagReq.onsuccess = e => resolve(e.target.result);
                                    addTagReq.onerror = e => reject(e.target.error);
                                });
                            }
                        })
                        .then(tagId => {
                            // chat_knowledge_tag にレコード挿入
                            const relationData = {
                                knowledge_id: knowledgeId,
                                tag_id: tagId,
                                created_at: new Date().toISOString(),
                                updated_at: new Date().toISOString()
                            };
                            knowledgeTagStore.add(relationData);
                        })
                        .catch(err => console.error("タグの処理エラー:", err));
                };

                transaction.oncomplete = function() {
                    console.log("データが正常に挿入されました");
                    displayDBContents();
                };

                transaction.onerror = function(event) {
                    console.error("トランザクションエラー:", event.target.error);
                };
            });
        });

        // 挿入後または手動でDB内容を表示する関数（デバッグ用）
        function displayDBContents() {
            window.getChatKnowledgeDB(function(db) {
                const objectStores = ["chat_knowledge", "chat_categories", "chat_tags", "chat_knowledge_category", "chat_knowledge_tag"];
                const container = document.getElementById("db-content");
                container.innerHTML = "";
                objectStores.forEach(storeName => {
                    const transaction = db.transaction(storeName, "readonly");
                    const store = transaction.objectStore(storeName);
                    const req = store.getAll();
                    req.onsuccess = function(e) {
                        const data = e.target.result;
                        const div = document.createElement("div");
                        div.innerHTML = `<h2>${storeName}</h2><pre>${JSON.stringify(data, null, 2)}</pre>`;
                        container.appendChild(div);
                    };
                });
            });
        }

        // 初回表示用（オプション）
        document.addEventListener("DOMContentLoaded", displayDBContents);
</script>

<a href="/main" class="btn return-btn">return to main</a>

<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => console.log('Service Worker registered with scope: ', registration.scope))
            .catch(error => console.log('Service Worker registration failed:', error));
    }
</script>
</body>
</html>
