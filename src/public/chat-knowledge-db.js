(function() {
    const dbName = "ChatKnowledgeDB";
    const dbVersion = 1;
    const request = indexedDB.open(dbName, dbVersion);

    request.onupgradeneeded = function(event) {
        const db = event.target.result;
        console.log("Upgrading database...");

        // 1. chat_knowledge (知識情報)
        if (!db.objectStoreNames.contains("chat_knowledge")) {
            const knowledgeStore = db.createObjectStore("chat_knowledge", { keyPath: "id", autoIncrement: true });
            knowledgeStore.createIndex("title", "title", { unique: false });
            knowledgeStore.createIndex("created_at", "created_at", { unique: false });
            knowledgeStore.createIndex("updated_at", "updated_at", { unique: false });
            knowledgeStore.createIndex("deleted_at", "deleted_at", { unique: false });
        }

        // 2. chat_tags (タグ情報)
        if (!db.objectStoreNames.contains("chat_tags")) {
            const tagsStore = db.createObjectStore("chat_tags", { keyPath: "id", autoIncrement: true });
            tagsStore.createIndex("name", "name", { unique: true });
        }

        // 3. chat_categories (カテゴリー情報)
        if (!db.objectStoreNames.contains("chat_categories")) {
            const categoriesStore = db.createObjectStore("chat_categories", { keyPath: "id", autoIncrement: true });
            categoriesStore.createIndex("name", "name", { unique: true });
        }

        // 4. chat_knowledge_tag (知識とタグの中間テーブル)
        if (!db.objectStoreNames.contains("chat_knowledge_tag")) {
            const knowledgeTagStore = db.createObjectStore("chat_knowledge_tag", { keyPath: "id", autoIncrement: true });
            knowledgeTagStore.createIndex("knowledge_id", "knowledge_id", { unique: false });
            knowledgeTagStore.createIndex("tag_id", "tag_id", { unique: false });
            knowledgeTagStore.createIndex("knowledge_tag", ["knowledge_id", "tag_id"], { unique: true });
        }

        // 5. chat_knowledge_category (知識とカテゴリーの中間テーブル)
        if (!db.objectStoreNames.contains("chat_knowledge_category")) {
            const knowledgeCategoryStore = db.createObjectStore("chat_knowledge_category", { keyPath: "id", autoIncrement: true });
            knowledgeCategoryStore.createIndex("knowledge_id", "knowledge_id", { unique: false });
            knowledgeCategoryStore.createIndex("category_id", "category_id", { unique: false });
            knowledgeCategoryStore.createIndex("knowledge_category", ["knowledge_id", "category_id"], { unique: true });
        }
    };

    request.onsuccess = function(event) {
        const db = event.target.result;
        console.log("Database initialized successfully:", db);
    };

    request.onerror = function(event) {
        console.error("Error initializing database:", event.target.error);
    };

    // グローバル関数として DB 接続を取得できるように公開
    window.getChatKnowledgeDB = function(callback) {
        const req = indexedDB.open(dbName, dbVersion);
        req.onsuccess = function(event) {
            callback(event.target.result);
        };
        req.onerror = function(event) {
            console.error("Failed to open database:", event.target.error);
        };
    };
})();
