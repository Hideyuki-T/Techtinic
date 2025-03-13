@extends('layouts.app')

@section('title', 'キャッシュされた知識一覧 - Techtinic')

@section('content')
    <h1>キャッシュされた知識一覧</h1>

    <!-- IndexedDB 同期状態を示すインジケータ -->
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
        document.addEventListener("DOMContentLoaded", function() {
            displayKnowledgeData();
        });

        function onIndexedDbSynchronized() {
            var statusEl = document.getElementById('indexeddb-status');
            statusEl.style.display = 'block';
            statusEl.innerText = 'Data Synchronized';
        }
    </script>
@endsection
