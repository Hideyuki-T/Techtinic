<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Laravel Tetris</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; }
        #gameCanvas { background: #eee; display: block; margin: 20px auto; border: 2px solid #333; }
    </style>
</head>
<body>
<h1>テトリス</h1>
<canvas id="gameCanvas"></canvas>
<div id="score-area">
    <input type="text" id="player_name" placeholder="プレイヤー名">
    <button id="submit-score">スコアを送信</button>
</div>
<!-- type="module" で ESモジュールとして読み込む -->
<script type="module" src="{{ asset('js/tetris/main.js') }}"></script>
</body>
</html>
