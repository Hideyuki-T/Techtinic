<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IndexedDB Sync Example</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
</head>
<body>
<h1>IndexedDB Sync Example</h1>
<p>サーバーからデータを取得してIndexedDBに同期</p>

<!-- IndexedDBの内容を表示するコンテナ -->
<div id="db-content">
    <!-- データはここに表示されます -->
</div>

<!-- 静的ファイルとしてルートからJSを読み込み -->
<script type="module" src="{{ url('/chat/indexedDBUtil.js') }}"></script>

<a href="/main" class="btn return-btn">return to main</a>

<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('Service Worker registered with scope: ', registration.scope);
            })
            .catch(error => {
                console.log('Service Worker registration failed:', error);
            });
    }
</script>
</body>
</html>
