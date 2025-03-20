<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>URL</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
</head>
<body>
<h1>お気に入り</h1>
<a href="https://qiita.com/yastinbieber/items/d296eae25a5b487ce3fb" target="_blank">Qiitaのスクレイピング記事</a><br>
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
