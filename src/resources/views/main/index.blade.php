<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="{{ asset('css/mainPageStyles.css') }}">
    <!-- PWA用のmanifestファイルの読み込み -->
    <link rel="manifest" href="/manifest.json">
    <!-- テーマカラー設定 -->
    <meta name="theme-color" content="#317EFB">
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

<!-- アプリインストール用ボタン -->
<button id="installBtn" style="display: none;">アプリをインストール</button>

<script>
    // インストールプロンプト制御用
    let deferredPrompt;
    const installBtn = document.getElementById('installBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installBtn.style.display = 'block';
    });

    installBtn.addEventListener('click', () => {
        installBtn.style.display = 'none';
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
        });
    });

    // サービスワーカーの登録
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
