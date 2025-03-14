@extends('layouts.app')

@section('title', 'キャッシュされた知識一覧 - Techtinic')

@section('content')
    <h1>IndexedDB</h1>

    {{-- IndexedDB 同期状態を示すインジケータ、初期状態では非表示（display:none）--}}
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
            //DOMの読み込みが完了したら、IndexedDBから知識データを取得して表示するための関数 displayKnowledgeData() を実行
        });

        function onIndexedDbSynchronized() {
            var statusEl = document.getElementById('indexeddb-status');
            statusEl.style.display = 'block';
            statusEl.innerText = 'Data Synchronized';
            // IndexedDBとのデータ同期が完了した際に呼び出される関数
            // 対象のインジケータ(div#indexeddb-status)を取得し、表示状態にしてテキストを「Data Synchronized」に変更する。
        }
    </script>
@endsection
