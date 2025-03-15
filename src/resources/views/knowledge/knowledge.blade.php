@extends('layouts.app')

@section('title', 'キャッシュされた知識一覧 - Techtinic')

@section('content')
    <h1>IndexedDB</h1>

    {{-- 切り替えボタン --}}
    <div id="view-toggle" style="margin-bottom: 10px;">
        <button id="listViewBtn" class="btn">一覧表示</button>
        <button id="categoryViewBtn" class="btn">カテゴリー別表示</button>
    </div>

    {{-- IndexedDB 同期状態を示すインジケータ、初期状態では非表示 --}}
    <div id="indexeddb-status" style="display:none; background: #dff0d8; color: #3c763d; padding: 10px; text-align: center; margin-bottom: 10px;">
        Data Synchronized
    </div>

    <div id="knowledge-list">
        <!-- IndexedDB から取得した知識データがここに表示される -->
    </div>

    <p><a href="/chat" class="btn">チャット画面に戻る</a></p>
@endsection

@section('scripts')
    <script type="module" src="/js/sync.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            // 初期表示は一覧表示
            await displayKnowledgeData();

            // 表示切替ボタンのイベントリスナーを設定
            const listViewBtn = document.getElementById('listViewBtn');
            const categoryViewBtn = document.getElementById('categoryViewBtn');
            listViewBtn.addEventListener('click', async () => {
                await displayKnowledgeData();
            });
            categoryViewBtn.addEventListener('click', async () => {
                await displayKnowledgeByCategory();
            });
        });

        function onIndexedDbSynchronized() {
            var statusEl = document.getElementById('indexeddb-status');
            statusEl.style.display = 'block';
            statusEl.innerText = 'Data Synchronized';
        }
    </script>
@endsection
