<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAME!</title>
    <link rel="stylesheet" href="{{ asset('css/gamePageStyles.css') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
</head>
<body>
<h1>ようこそ！ゲームページへ</h1>
<div class="container">
    <div class="card">
        <h2>数独</h2>
        <p>論理を駆使して数字を埋める頭脳パズル</p>
        <a href="{{ url('/sudoku') }}">遊ぶ！</a>
    </div>
    <div class="card">
        <h2>テトリス</h2>
        <p>ブロックを積み上げてラインを消す定番パズルゲーム</p>
        <a href="{{ url('/tetris') }}">遊ぶ！</a>
    </div>
    <button onclick="alert('ボタンがクリックされました！')">クリック</button>
    <a href="/main" class="btn">return to main</a>
</div>
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
