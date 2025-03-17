// sync.js をモジュールとして読み込みます
import { openDB } from '/js/idb.min.js';
window.idb = { openDB };

console.log("sync.js loaded");
console.log("window.idb:", window.idb);

// グローバル定数
const ITEMS_PER_PAGE = 10;
window.ITEMS_PER_PAGE = ITEMS_PER_PAGE;

/*--------------------------------------
  共通のヘルパー関数
--------------------------------------*/
function fetchWithTimeout(resource, options = {}) {
    const { timeout = 10000 } = options; // 10秒に設定
    return Promise.race([
        fetch(resource, options),
        new Promise((_, reject) =>
            setTimeout(() => reject(new Error('タイムアウト')), timeout)
        )
    ]);
}

async function checkServerOnlineStatus() {
    const statusEndpoint = '/api/system/status';
    try {
        const response = await Promise.race([
            fetchWithTimeout(statusEndpoint, { timeout: 10000 }),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('タイムアウト')), 10000)
            )
        ]);
        if (!response.headers.get('Content-Type')?.includes('application/json')) {
            throw new Error("Invalid response format");
        }
        const data = await response.json();
        return data.online;
    } catch (error) {
        console.error("オンライン状態チェック失敗:", error);
        return false;
    }
}

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

function loadPopupCSS() {
    if (!document.getElementById('popup-css')) {
        const head = document.head || document.getElementsByTagName('head')[0] || document.body;
        const link = document.createElement('link');
        link.id = 'popup-css';
        link.rel = 'stylesheet';
        link.href = 'css/popup.css';
        head.appendChild(link);
    }
}
window.loadPopupCSS = loadPopupCSS;

/*--------------------------------------
  IndexedDB 初期化（バージョン2に統一）
--------------------------------------*/
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
window.initDB = initDB;

/*--------------------------------------
  タグ取得／削除
--------------------------------------*/
async function getExistingTags() {
    try {
        const response = await fetch('/api/tags');
        return await response.json();
    } catch (error) {
        console.error("既存タグの取得に失敗:", error);
        return [];
    }
}
window.getExistingTags = getExistingTags;

async function deleteTag(tagId) {
    if (!confirm("本当にこのタグを削除してよろしいですか？")) {
        return;
    }
    try {
        const deleteUrl = `/api/tags/${tagId}`;
        const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (response.ok) {
            alert("タグが削除されました。");
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

/*--------------------------------------
  チャット機能 (IndexedDBのみ)
--------------------------------------*/
// IndexedDB にメッセージを保存
async function saveChatMessage(message) {
    const db = await initDB();
    const tx = db.transaction('chatMessages', 'readwrite');
    await tx.objectStore('chatMessages').add({ message, timestamp: Date.now() });
    await tx.done;
    console.log("チャットメッセージが IndexedDB に保存されました。");
}

// IndexedDB から全チャットメッセージを取得
async function getChatMessages() {
    const db = await initDB();
    return await db.getAll('chatMessages');
}

// UI 更新関数
function updateChatUI(messages) {
    const chatList = document.getElementById('chat-list');
    if (chatList) {
        chatList.innerHTML = '';
        messages.sort((a, b) => a.timestamp - b.timestamp);
        messages.forEach(msg => {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'chat-message';
            msgDiv.textContent = `${new Date(msg.timestamp).toLocaleTimeString()}: ${msg.message}`;
            chatList.appendChild(msgDiv);
        });
    }
}
window.updateChatUI = updateChatUI;

// displayChatMessages: IndexedDBからメッセージを取得してUI更新するラッパー
async function displayChatMessages() {
    const messages = await getChatMessages();
    updateChatUI(messages);
}
window.displayChatMessages = displayChatMessages;

// チャット送信処理：IndexedDB に保存して UI 更新
async function sendChatMessage(message) {
    try {
        await saveChatMessage(message);
        const messages = await getChatMessages();
        updateChatUI(messages);
    } catch (error) {
        console.error("チャットメッセージ送信エラー:", error);
    }
}
window.sendChatMessage = sendChatMessage;

/*--------------------------------------
  同期処理（サーバーとIndexedDBの同期）
--------------------------------------*/
async function syncDataFromPC() {
    const hostname = window.location.hostname;
    console.log("使用するホスト名:", hostname);
    console.log("同期処理開始");
    let syncUrl = `https://${hostname}:8080/api/sync`;
    try {
        const response = await fetchWithTimeout(syncUrl, { timeout: 10000 });
        if (!response.headers.get('Content-Type')?.includes('application/json')) {
            throw new Error("JSON 形式ではありません");
        }
        const data = await response.json();
        console.log("同期データ取得:", data);
        await saveKnowledgeData(data);
        console.log("同期完了");
        onIndexedDbSynchronized();
    } catch (error) {
        console.error("同期失敗:", error);
    }
}
window.syncDataFromPC = syncDataFromPC;

/*--------------------------------------
  知識データ処理（オンライン連携用）
--------------------------------------*/
async function getKnowledgeData() {
    const db = await initDB();
    return await db.getAll('knowledge');
}
window.getKnowledgeData = getKnowledgeData;

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
window.saveKnowledgeData = saveKnowledgeData;

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
window.displayKnowledgeData = displayKnowledgeData;

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
window.displayKnowledgeByDropdown = displayKnowledgeByDropdown;

/*--------------------------------------
  編集・削除関連（知識データ処理）
--------------------------------------*/
function editKnowledgeItem(id) {
    if (!navigator.onLine) {
        alert("編集はオンライン時のみ利用可能です。");
        return;
    }
    openEditForm(id);
}
window.editKnowledgeItem = editKnowledgeItem;

async function openEditForm(id) {
    loadPopupCSS();
    const db = await initDB();
    const tx = db.transaction('knowledge', 'readonly');
    const store = tx.objectStore('knowledge');
    const item = await store.get(id);
    if (!item) {
        alert("該当データが見つかりません。");
        return;
    }
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
window.openEditForm = openEditForm;

function toggleDropdown() {
    const dropdownMenu = document.getElementById('dropdownMenu');
    if (dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '') {
        dropdownMenu.style.display = 'block';
    } else {
        dropdownMenu.style.display = 'none';
    }
}
window.toggleDropdown = toggleDropdown;

function closeEditForm() {
    const container = document.getElementById('editFormContainer');
    if (container) {
        container.remove();
    }
}
window.closeEditForm = closeEditForm;

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
window.submitEdit = submitEdit;

/*--------------------------------------
  DOMContentLoaded の初期処理
--------------------------------------*/
document.addEventListener("DOMContentLoaded", async () => {
    // 知識データの表示
    await displayKnowledgeData();
    // チャットメッセージの初回表示
    await displayChatMessages();

    // チャット送信ボタンの設定
    const chatSendBtn = document.getElementById('chat-send-btn');
    const chatInput = document.getElementById('chat-input');
    if (chatSendBtn && chatInput) {
        chatSendBtn.addEventListener('click', async () => {
            const message = chatInput.value.trim();
            if (message) {
                await sendChatMessage(message);
                chatInput.value = '';
            }
        });
    }

    // 知識データ表示切替用ボタンの設定
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

    // オンラインの場合、同期処理を開始する
    const serverOnline = await checkServerOnlineStatus();
    if (serverOnline) {
        console.log("Online: 同期処理を開始します。");
        await syncDataFromPC();
    } else {
        console.log("Offline: 同期処理はスキップします。");
    }
});

// 最後に必要な関数をグローバルに登録
window.saveKnowledgeData = saveKnowledgeData;
window.getExistingTags = getExistingTags;
window.editKnowledgeItem = editKnowledgeItem;
window.openEditForm = openEditForm;
window.toggleDropdown = toggleDropdown;
window.closeEditForm = closeEditForm;
window.submitEdit = submitEdit;
window.displayKnowledgeData = displayKnowledgeData;
window.displayKnowledgeByDropdown = displayKnowledgeByDropdown;

/*--------------------------------------
  同期完了時のインジケータ更新用関数
--------------------------------------*/
function onIndexedDbSynchronized() {
    const statusEl = document.getElementById('indexeddb-status');
    if (statusEl) {
        statusEl.style.display = 'block';
        statusEl.innerText = 'Data Synchronized';
    }
}
window.onIndexedDbSynchronized = onIndexedDbSynchronized;
