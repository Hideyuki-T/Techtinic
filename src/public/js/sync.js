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

// 追加: サーバーのオンライン状態をチェックする関数
async function checkServerOnlineStatus() {
    const statusEndpoint = '/api/system/status';
    try {
        const response = await Promise.race([
            fetchWithTimeout(statusEndpoint, { timeout: 10000 }),
            new Promise((_, reject) => setTimeout(() => reject(new Error('タイムアウト')), 10000))
        ]);
        if (!response.headers.get('Content-Type')?.includes('application/json')) {
            throw new Error("Invalid response format");
        }
        const data = await response.json();
        return data.online; // true か false を返す
    } catch (error) {
        console.error("オンライン状態チェック失敗:", error);
        return false; // エラー時はオフラインとみなす
    }
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

// CSS を動的に読み込む関数を定義（グローバルに公開）
function loadPopupCSS() {
    if (!document.getElementById('popup-css')) {
        // document.head が存在しない場合は document.body に追加
        const head = document.head || document.getElementsByTagName('head')[0] || document.body;
        const link = document.createElement('link');
        link.id = 'popup-css';
        link.rel = 'stylesheet';
        link.href = 'css/popup.css';
        head.appendChild(link);
    }
}
window.loadPopupCSS = loadPopupCSS;

// タグ削除用の関数（グローバルに公開）
async function deleteTag(tagId) {
    if (!confirm("本当にこのタグを削除してよろしいですか？")) {
        return;
    }
    try {
        const deleteUrl = `/api/tags/${tagId}`;
        const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (response.ok) {
            alert("タグが削除されました。");
            // タグ削除後は、再度タグ一覧を取得するためにページリロード（または再描画処理）する
            location.reload();
        } else {
            console.error("タグ削除に失敗:", result);
            alert("タグ削除に失敗しました。");
        }
    } catch (error) {
        console.error("タグ削除中にエラーが発生しました:", error);
        alert("タグ削除中にエラーが発生しました。");
    }
}
window.deleteTag = deleteTag;

// DOMContentLoaded 時にサーバーのオンライン状態をチェックし、オンラインなら同期処理を開始する
document.addEventListener("DOMContentLoaded", async () => {
    const serverOnline = await checkServerOnlineStatus();
    if (serverOnline) {
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

// 削除機能：登録されている知識情報を削除する関数（指定した id のアイテムを削除）
async function deleteKnowledgeItem(id) {
    if (!confirm("本当に削除しても良いかな？")) {
        return;
    }
    try {
        // サーバー側の削除APIを呼び出す
        const deleteUrl = `/api/knowledge/${id}`; // ルート定義に合わせる
        const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (response.ok) {
            console.log("サーバー上で削除成功:", result);
            // サーバーでの削除が成功したら、IndexedDBからも削除する
            const db = await initDB();
            const tx = db.transaction('knowledge', 'readwrite');
            await tx.objectStore('knowledge').delete(id);
            await tx.done;
            console.log("IndexedDBからも削除したよ。id:", id);
            await displayKnowledgeData();
        } else {
            console.error("サーバー側で削除に失敗だよ:", result);
        }
    } catch (error) {
        console.error("削除処理中にエラーが発生したよ:", error);
    }
}

// 既存タグ一覧を取得する関数
async function getExistingTags() {
    try {
        const response = await fetch('/api/tags'); // API エンドポイントが /api/tags と仮定
        return await response.json(); // 例：[{id:1, name:"docker"}, ...]
    } catch (error) {
        console.error("既存タグの取得に失敗:", error);
        return [];
    }
}

// 編集機能：編集はオンライン時のみ可能
function editKnowledgeItem(id) {
    if (!navigator.onLine) {
        alert("編集はオンライン時のみ利用可能です。");
        return;
    }
    openEditForm(id);
}

// 編集フォームを動的に生成して表示する関数【修正版だYO！】
async function openEditForm(id) {
    // CSS を読み込む
    loadPopupCSS();

    const db = await initDB();
    const tx = db.transaction('knowledge', 'readonly');
    const store = tx.objectStore('knowledge');
    const item = await store.get(id);
    if (!item) {
        alert("該当データが見つかりません。");
        return;
    }
    // 既存タグのチェックボックス＋削除ボタンHTML生成
    const existingTags = await getExistingTags();
    let checkboxesHtml = '';
    existingTags.forEach(tag => {
        let checked = (item.tags && item.tags.some(t => t.id == tag.id)) ? 'checked' : '';
        checkboxesHtml += `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <label style="flex:1; padding: 2px 5px;">
                    <input type="checkbox" name="existing_tags[]" value="${tag.id}" ${checked}>
                    ${tag.name}
                </label>
                <button type="button" onclick="deleteTag(${tag.id})" style="margin-left:5px;">削除</button>
            </div>
        `;
    });
    // HTML構造を組み立て
    const formHtml = `
        <div id="editFormContainer">
            <h3>知識情報の編集</h3>
            <form onsubmit="submitEdit(event, ${id})">
                <div class="form-group">
                    <label for="editCategory">カテゴリー</label>
                    <input type="text" name="category" id="editCategory" value="${ item.category || (item.categories ? item.categories.map(cat => cat.name).join(', ') : '') }" placeholder="例: dockerコマンド" required>
                </div>
                <div class="form-group">
                    <label for="editTitle">タイトル</label>
                    <input type="text" name="title" id="editTitle" value="${item.title}" placeholder="例: 起動済のコンテナ一覧の表示" required>
                </div>
                <div class="form-group">
                    <label for="editContent">本文</label>
                    <textarea name="content" id="editContent" rows="4" placeholder="例: docker ps と入力して、起動中のコンテナ一覧を表示する" required>${item.content}</textarea>
                </div>
                <div class="form-group">
                    <label>既存のタグから選択 (複数選択可)</label>
                    <div class="dropdown" style="position: relative; display: inline-block; width:100%;">
                        <button type="button" id="dropdownButton" onclick="toggleDropdown()">タグを選択</button>
                        <div id="dropdownMenu" style="display: none; position: absolute; background: #fff; box-shadow: 0px 8px 16px rgba(0,0,0,0.2); z-index: 1000; max-height:200px; overflow-y:auto; width:100%;">
                            ${checkboxesHtml}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="editNewTags">新しいタグ (カンマ区切りで入力)</label>
                    <input type="text" name="new_tags" id="editNewTags" placeholder="例: docker, コンテナ, 状態確認">
                </div>
                <button type="submit">更新する</button>
                <button type="button" onclick="closeEditForm()">キャンセル</button>
            </form>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', formHtml);
}

// プルダウンの表示/非表示を切り替える関数（グローバルに公開）
function toggleDropdown() {
    const dropdownMenu = document.getElementById('dropdownMenu');
    if (dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '') {
        dropdownMenu.style.display = 'block';
    } else {
        dropdownMenu.style.display = 'none';
    }
}
window.toggleDropdown = toggleDropdown;

// 編集フォームを閉じる関数
function closeEditForm() {
    const container = document.getElementById('editFormContainer');
    if (container) {
        container.remove();
    }
}

// 編集内容をオンラインで更新する関数（フォーム送信時に呼ばれる）
async function submitEdit(event, id) {
    event.preventDefault();
    const newCategory = document.getElementById('editCategory').value.trim();
    const newTitle = document.getElementById('editTitle').value.trim();
    const newContent = document.getElementById('editContent').value.trim();
    const existingTagsElements = document.querySelectorAll('input[name="existing_tags[]"]:checked');
    let existingTags = [];
    existingTagsElements.forEach(el => {
        existingTags.push(el.value);
    });
    const newTags = document.getElementById('editNewTags').value.trim();
    if (!newCategory || !newTitle || !newContent) {
        alert("カテゴリー、タイトル、本文は必須です。");
        return;
    }
    try {
        // オンライン編集の場合、サーバー側の更新API（PUT）に送信
        const updateUrl = `/api/knowledge/${id}`;
        const response = await fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                category: newCategory,
                title: newTitle,
                content: newContent,
                existing_tags: existingTags,
                new_tags: newTags
            })
        });
        const result = await response.json();
        if (response.ok) {
            console.log("サーバー上で更新成功:", result);
            // サーバー更新成功後、IndexedDBも更新（任意）
            const db = await initDB();
            const tx = db.transaction('knowledge', 'readwrite');
            let item = await tx.objectStore('knowledge').get(id);
            if (item) {
                item.category = newCategory;
                item.title = newTitle;
                item.content = newContent;
                await tx.objectStore('knowledge').put(item);
            }
            await tx.done;
            alert("編集が完了しました。");
            closeEditForm();
            await displayKnowledgeData();
        } else {
            console.error("サーバー側で更新に失敗:", result);
        }
    } catch (error) {
        console.error("更新処理中にエラーが発生:", error);
    }
}

const ITEMS_PER_PAGE = 10;

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
            listDiv.appendChild(sortSelect);
            let itemsContainer = document.createElement('div');
            itemsContainer.id = 'itemsContainer';
            listDiv.appendChild(itemsContainer);
            data = sortByTimestamp(data, 'asc');
            let currentPage = 1;
            const totalPages = Math.ceil(data.length / ITEMS_PER_PAGE);
            function renderPaginationControls() {
                let paginationDiv = document.getElementById('pagination');
                if (!paginationDiv) {
                    paginationDiv = document.createElement('div');
                    paginationDiv.id = 'pagination';
                    paginationDiv.style.marginTop = '10px';
                    listDiv.appendChild(paginationDiv);
                }
                paginationDiv.innerHTML = '';
                const prevBtn = document.createElement('button');
                prevBtn.textContent = '前へ';
                prevBtn.disabled = currentPage === 1;
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        renderList(data);
                        renderPaginationControls();
                    }
                });
                paginationDiv.appendChild(prevBtn);
                const pageInfo = document.createElement('span');
                pageInfo.textContent = ` ${currentPage} / ${totalPages} `;
                paginationDiv.appendChild(pageInfo);
                const nextBtn = document.createElement('button');
                nextBtn.textContent = '次へ';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderList(data);
                        renderPaginationControls();
                    }
                });
                paginationDiv.appendChild(nextBtn);
            }
            function renderList(dataArray) {
                itemsContainer.innerHTML = '';
                const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
                const pageItems = dataArray.slice(startIndex, startIndex + ITEMS_PER_PAGE);
                pageItems.forEach(item => {
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
                        ? `<div class="timestamp">timestamp: ${new Date(item.created_at).toLocaleString()}</div>`
                        : '';
                    itemDiv.innerHTML = `
                        <div class="categories">${categoriesHTML}</div>
                        <div class="title"><strong>title: ${item.title}</strong></div>
                        <div class="content">content:<br>${item.content.replace(/\n/g, '<br>')}</div>
                        ${tagsHTML}
                        ${timestampHTML}
                        <div class="actions">
                            <button onclick="editKnowledgeItem(${item.id})">編集</button>
                            <button onclick="deleteKnowledgeItem(${item.id})">削除</button>
                        </div>
                    `;
                    itemsContainer.appendChild(itemDiv);
                });
            }
            sortSelect.addEventListener('change', () => {
                data = sortByTimestamp(data, sortSelect.value);
                currentPage = 1;
                renderList(data);
                renderPaginationControls();
            });
            renderList(data);
            renderPaginationControls();
        }
    } catch (error) {
        console.error("知識データの表示に失敗しました:", error);
    }
}

function sortByTimestamp(dataArray, order = 'asc') {
    return dataArray.sort((a, b) => {
        const timeA = new Date(a.created_at).getTime();
        const timeB = new Date(b.created_at).getTime();
        return order === 'asc' ? timeA - timeB : timeB - timeA;
    });
}

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
            const selectEl = document.createElement('select');
            selectEl.id = 'categorySelect';
            selectEl.style.marginBottom = '10px';
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'すべて表示';
            selectEl.appendChild(allOption);
            for (const category in categoryGroups) {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                selectEl.appendChild(option);
            }
            listDiv.appendChild(selectEl);
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
            const itemsContainer = document.createElement('div');
            itemsContainer.id = 'itemsContainer';
            listDiv.appendChild(itemsContainer);
            const ITEMS_PER_PAGE = 10;
            let currentPage = 1;
            function displayItems(selectedCategory, sortOrder) {
                let itemsToDisplay;
                if (selectedCategory === 'all') {
                    itemsToDisplay = data;
                } else {
                    itemsToDisplay = categoryGroups[selectedCategory] || [];
                }
                itemsToDisplay = sortByTimestamp(itemsToDisplay, sortOrder);
                const totalPages = Math.ceil(itemsToDisplay.length / ITEMS_PER_PAGE);
                currentPage = 1;
                function renderItems() {
                    itemsContainer.innerHTML = '';
                    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
                    const pageItems = itemsToDisplay.slice(startIndex, startIndex + ITEMS_PER_PAGE);
                    pageItems.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'knowledge-item';
                        const timestampHTML = item.created_at
                            ? `<div class="timestamp">timestamp: ${new Date(item.created_at).toLocaleString()}</div>`
                            : '';
                        itemDiv.innerHTML = `
                            <div class="title"><strong>title: ${item.title}</strong></div>
                            <div class="content">content:<br>${item.content.replace(/\n/g, '<br>')}</div>
                            ${timestampHTML}
                        `;
                        itemsContainer.appendChild(itemDiv);
                    });
                    let paginationDiv = document.getElementById('dropdownPagination');
                    if (!paginationDiv) {
                        paginationDiv = document.createElement('div');
                        paginationDiv.id = 'dropdownPagination';
                        paginationDiv.style.marginTop = '10px';
                        itemsContainer.parentNode.appendChild(paginationDiv);
                    }
                    paginationDiv.innerHTML = '';
                    const prevBtn = document.createElement('button');
                    prevBtn.textContent = '前へ';
                    prevBtn.disabled = currentPage === 1;
                    prevBtn.addEventListener('click', () => {
                        if (currentPage > 1) {
                            currentPage--;
                            renderItems();
                        }
                    });
                    paginationDiv.appendChild(prevBtn);
                    const pageInfo = document.createElement('span');
                    pageInfo.textContent = ` ${currentPage} / ${totalPages} `;
                    paginationDiv.appendChild(pageInfo);
                    const nextBtn = document.createElement('button');
                    nextBtn.textContent = '次へ';
                    nextBtn.disabled = currentPage === totalPages;
                    nextBtn.addEventListener('click', () => {
                        if (currentPage < totalPages) {
                            currentPage++;
                            renderItems();
                        }
                    });
                    paginationDiv.appendChild(nextBtn);
                }
                renderItems();
            }
            displayItems('all', 'asc');
            selectEl.addEventListener('change', function() {
                displayItems(this.value, sortSelect.value);
            });
            sortSelect.addEventListener('change', function() {
                displayItems(selectEl.value, this.value);
            });
        }
    } catch (error) {
        console.error("知識データのプルダウン表示に失敗しました((汗:", error);
    }
}

window.initDB = initDB;
window.saveKnowledgeData = saveKnowledgeData;
window.getKnowledgeData = getKnowledgeData;
window.syncDataFromPC = syncDataFromPC;
window.displayKnowledgeData = displayKnowledgeData;
window.deleteKnowledgeItem = deleteKnowledgeItem;
window.editKnowledgeItem = editKnowledgeItem;
window.displayKnowledgeByDropdown = displayKnowledgeByDropdown;
window.openEditForm = openEditForm;
window.closeEditForm = closeEditForm;
window.submitEdit = submitEdit;
window.toggleDropdown = toggleDropdown;
