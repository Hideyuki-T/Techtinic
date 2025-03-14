// sync.js をモジュールとして読み込みます
import { openDB } from '/js/idb.min.js';
// 必要に応じてグローバルに公開（オプション）
window.idb = { openDB };

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
    // 初期表示は一覧表示
    await displayKnowledgeData();

    // 表示切替用ボタンのイベントリスナー設定
    const listViewBtn = document.getElementById('listViewBtn');
    const categoryViewBtn = document.getElementById('categoryViewBtn');
    if (listViewBtn && categoryViewBtn) {
        listViewBtn.addEventListener('click', async () => {
            await displayKnowledgeData();
        });
        categoryViewBtn.addEventListener('click', async () => {
            await displayKnowledgeByDropdown();
        });
    }
});

async function syncDataFromPC() {
    const hostname = window.location.hostname;
    console.log("使用するホスト名:", hostname);
    // 絶対URLを組み立てる（ポート番号が必要な場合は適宜調整）
    let syncUrl = `https://${hostname}:8080/api/sync`;
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
    const db = await openDB('techtinic-db', 1, {
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

// 登録されている知識情報を削除する関数（指定した id のアイテムを削除）
async function deleteKnowledgeItem(id) {
    if (!confirm("本当に削除しても良いかな？")) {
        return;
    }
    const db = await initDB();
    const tx = db.transaction('knowledge', 'readwrite');
    await tx.objectStore('knowledge').delete(id);
    await tx.done;
    console.log("知識情報を削除したよ。id:", id);
    await displayKnowledgeData();
}

// ソート（並び替え）用関数：昇順 or 降順に並び替える
function sortByTimestamp(data, order = 'asc') {
    return data.sort((a, b) => {
        const timeA = new Date(a.created_at).getTime();
        const timeB = new Date(b.created_at).getTime();
        return order === 'asc' ? timeA - timeB : timeB - timeA;
    });
}

// UI に IndexedDB のデータを一覧表示する関数（ソート機能付き）
async function displayKnowledgeData() {
    try {
        let data = await getKnowledgeData();
        const listDiv = document.getElementById('knowledge-list');
        if (listDiv) {
            listDiv.innerHTML = '';
            if (data.length === 0) {
                listDiv.innerHTML = '<p>何もキャッシュされてないよ。\(￣ー￣)/</p>';
                return;
            }

            // ソート用セレクトボックスを作成
            const sortSelect = document.createElement('select');
            sortSelect.id = 'sortSelect';
            sortSelect.style.marginBottom = '10px';
            const ascOption = document.createElement('option');
            ascOption.value = 'asc';
            ascOption.textContent = '昇順';
            const descOption = document.createElement('option');
            descOption.value = 'desc';
            descOption.textContent = '降順';
            sortSelect.appendChild(ascOption);
            sortSelect.appendChild(descOption);

            // ソートセレクトボックス変更時のイベント
            sortSelect.addEventListener('change', () => {
                data = sortByTimestamp(data, sortSelect.value);
                renderList(data);
            });

            listDiv.appendChild(sortSelect);

            // 初期ソート（昇順）
            data = sortByTimestamp(data, 'asc');

            // データ表示用のレンダリング関数
            function renderList(dataArray) {
                // 既存の表示部分（ソートセレクト以外）をクリア
                // sortSelectは listDiv の最初の子要素とする
                while (listDiv.childNodes.length > 1) {
                    listDiv.removeChild(listDiv.lastChild);
                }
                dataArray.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'knowledge-item';

                    let categoriesHTML = '';
                    if (item.categories && item.categories.length > 0) {
                        const categoryNames = item.categories.map(cat => cat.name).join(', ');
                        categoriesHTML = `<span class="categories">categories:【${categoryNames}】</span>`;
                    }

                    let tagsHTML = '';
                    if (item.tags && item.tags.length > 0) {
                        const tagNames = item.tags.map(tag => tag.name).join(', ');
                        tagsHTML = `<div class="tags"><small>tags:[${tagNames}]</small></div>`;
                    }

                    const timestampHTML = item.created_at
                        ? `<div class="timestamp">作成日時: ${new Date(item.created_at).toLocaleString()}</div>`
                        : '';

                    itemDiv.innerHTML = `
                        <div class="categories">${categoriesHTML}</div>
                        <div class="title"><strong>title: ${item.title}</strong></div>
                        <div class="content">content:<br>${item.content.replace(/\n/g, '<br>')}</div>
                        ${tagsHTML}
                        ${timestampHTML}
                        <div class="actions">
                            <button onclick="deleteKnowledgeItem(${item.id})">削除</button>
                        </div>
                    `;
                    listDiv.appendChild(itemDiv);
                });
            }

            // 初回レンダリング
            renderList(data);
        }
    } catch (error) {
        console.error("知識データの表示に失敗しました:", error);
    }
}

// カテゴリー選択用プルダウンで表示する関数（ソート機能付き）
async function displayKnowledgeByDropdown() {
    try {
        let data = await getKnowledgeData();
        const listDiv = document.getElementById('knowledge-list');
        if (listDiv) {
            listDiv.innerHTML = '';
            if (data.length === 0) {
                listDiv.innerHTML = '<p>キャッシュされた知識はありません。</p>';
                return;
            }
            // グループ化：カテゴリーごとにデータをまとめる
            const categoryGroups = {};
            data.forEach(item => {
                if (item.categories && item.categories.length > 0) {
                    item.categories.forEach(cat => {
                        if (!categoryGroups[cat.name]) {
                            categoryGroups[cat.name] = [];
                        }
                        categoryGroups[cat.name].push(item);
                    });
                } else {
                    if (!categoryGroups["未分類"]) {
                        categoryGroups["未分類"] = [];
                    }
                    categoryGroups["未分類"].push(item);
                }
            });

            // プルダウン（select要素）を作成
            const selectEl = document.createElement('select');
            selectEl.id = 'categorySelect';
            selectEl.style.marginBottom = '10px';

            // 「すべて表示」オプション
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'すべて表示';
            selectEl.appendChild(allOption);

            // グループ化された各カテゴリーをオプションとして追加
            for (const category in categoryGroups) {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                selectEl.appendChild(option);
            }

            listDiv.appendChild(selectEl);

            // ソート用セレクトボックスを作成（作成日時順）
            const sortSelect = document.createElement('select');
            sortSelect.id = 'dropdownSortSelect';
            sortSelect.style.marginBottom = '10px';
            const ascOption = document.createElement('option');
            ascOption.value = 'asc';
            ascOption.textContent = '昇順';
            const descOption = document.createElement('option');
            descOption.value = 'desc';
            descOption.textContent = '降順';
            sortSelect.appendChild(ascOption);
            sortSelect.appendChild(descOption);
            listDiv.appendChild(sortSelect);

            // アイテム表示用コンテナを作成
            const itemsContainer = document.createElement('div');
            itemsContainer.id = 'itemsContainer';
            listDiv.appendChild(itemsContainer);

            // 選択されたカテゴリーに応じてアイテムを表示する関数
            function displayItems(selectedCategory, sortOrder) {
                itemsContainer.innerHTML = '';
                let itemsToDisplay;
                if (selectedCategory === 'all') {
                    itemsToDisplay = data;
                } else {
                    itemsToDisplay = categoryGroups[selectedCategory] || [];
                }
                // ソート適用（作成日時に基づく）
                itemsToDisplay = sortByTimestamp(itemsToDisplay, sortOrder);
                itemsToDisplay.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'knowledge-item';
                    const timestampHTML = item.created_at
                        ? `<div class="timestamp">作成日時: ${new Date(item.created_at).toLocaleString()}</div>`
                        : '';
                    itemDiv.innerHTML = `
                        <div class="title"><strong>title: ${item.title}</strong></div>
                        <div class="content">content:<br>${item.content.replace(/\n/g, '<br>')}</div>
                        ${timestampHTML}
                    `;
                    itemsContainer.appendChild(itemDiv);
                });
            }

            // 初期表示は「すべて表示」と昇順
            displayItems('all', 'asc');

            // プルダウン変更時に表示を切り替え
            selectEl.addEventListener('change', function() {
                displayItems(this.value, sortSelect.value);
            });

            // ソートセレクト変更時に再表示
            sortSelect.addEventListener('change', function() {
                displayItems(selectEl.value, this.value);
            });
        }
    } catch (error) {
        console.error("知識データのプルダウン表示に失敗しました:", error);
    }
}

// グローバルに関数を公開する（必要な場合）
window.initDB = initDB;
window.saveKnowledgeData = saveKnowledgeData;
window.getKnowledgeData = getKnowledgeData;
window.syncDataFromPC = syncDataFromPC;
window.displayKnowledgeData = displayKnowledgeData;
window.deleteKnowledgeItem = deleteKnowledgeItem;
window.displayKnowledgeByDropdown = displayKnowledgeByDropdown;
