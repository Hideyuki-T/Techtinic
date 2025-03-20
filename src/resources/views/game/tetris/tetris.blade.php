<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Laravel Tetris</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/tetrisPageStyles.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
</head>
<body>
<h1>テトリス</h1>

<!-- ゲーム制御ボタン -->
<div class="controls">
    <button id="start-btn">スタート</button>
    <button id="stop-btn">ストップ</button>
    <button id="reset-btn">リセット</button>
</div>

<!-- ゲーム描画用キャンバス -->
<canvas id="gameCanvas"></canvas>

<!-- 十字キーの方向ボタン -->
<div class="controls">
    <div class="dpad">
        <div class="dpad-row">
            <button id="up-btn">↑</button>
        </div>
        <div class="dpad-row">
            <button id="left-btn">←</button>
            <button id="down-btn">↓</button>
            <button id="right-btn">→</button>
        </div>
    </div>
</div>

<!-- スコア送信エリア -->
<div id="score-area" style="text-align: center; margin-top: 10px;">
    <input type="text" id="player_name" placeholder="プレイヤー名">
    <button id="submit-score">スコアを送信</button>
</div>

<a href="/game" class="btn">return to game</a>

<!-- ESモジュールとして main.js を読み込む -->
<script type="module" src="{{ asset('js/tetris/main.js') }}"></script>

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
