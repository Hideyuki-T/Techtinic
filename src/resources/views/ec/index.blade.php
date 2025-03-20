<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECサイト</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
    </style>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
</head>
<body>
<h1>ようこそ！ECサイトへ</h1>
<p>まだ作成途中だよ。</p>
<button onclick="alert('ボタンがクリックされました！')">クリック</button><br><br>
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
