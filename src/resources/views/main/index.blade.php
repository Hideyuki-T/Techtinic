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
<header id="quote-header">ここに名言が表示されるんだよ</header>
<div class="container">
    <div class="card">
        <h2>CHAT</h2>
        <p>対話形式メモ帳<br>↓<br>IndexedDB</p>
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
    <div>
    <div class="card">
        <h2>参考になるよ！</h2>
        <p>URL</p>
        <a href="{{ url('/url') }}">Check URL</a>
    </div>

<!-- アプリインストール用ボタン -->
<button id="installBtn" style="display: none;">インストール！</button>

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
<script>
    // quotes.jsonから名言のリストを取得する関数
    async function fetchQuotes() {
        try {
            const response = await fetch('/quotes.json');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return await response.json();
        } catch (error) {
            console.error('Error fetching quotes:', error);
            return [];
        }
    }

    // ランダムな名言をヘッダーに表示する関数
    function displayRandomQuote(quotes) {
        if (quotes.length === 0) return;
        const randomIndex = Math.floor(Math.random() * quotes.length);
        document.getElementById('quote-header').textContent = quotes[randomIndex];
    }

    // 名言を取得して、8秒ごとに更新
    fetchQuotes().then(quotes => {
        // 初回表示
        displayRandomQuote(quotes);
        // 5秒ごとに名言を更新
        setInterval(() => {
            displayRandomQuote(quotes);
        }, 8000);
    });
</script>
</div>
</div>
</body>
</html>
