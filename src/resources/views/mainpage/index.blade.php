<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="{{ asset('css/mainPageStyles.css') }}">
</head>
<body>

<h1>Welcome to My Projects</h1>
<div class="container">
    <div class="card">
        <h2>CHAT</h2>
        <p>対話形式メモ帳<br>↓<br>サービスワーカー＆IndexedDB</p>
        <a href="{{ url('/chat') }}">Go to Chat</a>
    </div>
    <div class="card">
        <h2>GAME</h2>
        <p>数独＆テトリス</p>
        <a href="{{ url('/game') }}">Play Game</a>
    </div>
    <div class="card">
        <h2>ECサイト</h2>
        <p>お店作成中...</p>
        <a href="{{ url('/ec') }}">Visit EC Site</a>
    </div>
</div>

</body>
</html>
