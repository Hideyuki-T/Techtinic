<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>キャッシュされた知識一覧 - Techtinic</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- axiosライブラリの読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="manifest" href="/manifest.json">
    <!-- idbライブラリを ESモジュール形式で読み込み、グローバルに公開 -->
    <script type="module">
        import { openDB } from "/js/idb.min.js";
        window.idb = { openDB };
    </script>
</head>
<body>
<h1>キャッシュされた知識一覧</h1>
<div id="knowledge-list">
    <!-- IndexedDB から取得した知識データがここに表示される -->
</div>
<!-- チャット画面へ戻るリンク -->
<p><a href="/chat" class="btn">チャット画面に戻る</a></p>

<!-- sync.js の読み込み（IndexedDB 関連の処理が含まれる） -->
<script src="/js/sync.js"></script>
<script>
    // ページ読み込み時に IndexedDB からデータを表示する
    document.addEventListener("DOMContentLoaded", displayKnowledgeData);
</script>
</body>
</html>
