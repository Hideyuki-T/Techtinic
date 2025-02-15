<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techtinic Chat</title>
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
<!-- 上部のリンクなど -->
<div style="margin-bottom: 10px;">
    <a href="/teach" class="btn">知識登録画面へ</a>
</div>
<h1>Techtinic Chat</h1>
<div id="chat-box">
    <!-- チャット履歴がここに表示される -->
</div>
<div id="selection-box"></div>
<input type="text" id="message" placeholder="メッセージを入力" style="width: 70%;">
<button onclick="sendMessage()">送信</button>

<!-- キャッシュされた知識一覧ページへのリンク -->
<p style="margin-top:20px;">
    <a href="/knowledge" class="btn">キャッシュされた知識を見る</a>
</p>

<!-- チャット機能のスクリプト -->
<script>
    var currentStage = "default";

    function sendMessage() {
        var messageInput = document.getElementById('message');
        var message = messageInput.value.trim();
        if (message === '') return;
        var chatBox = document.getElementById('chat-box');
        chatBox.innerHTML += '<div class="message"><strong>あなた:</strong> ' + message + '</div>';
        messageInput.value = '';
        document.getElementById('selection-box').innerHTML = '';
        document.getElementById('selection-box').style.display = 'none';
        sendToAPI(message, currentStage);
    }

    function sendToAPI(message, stage) {
        axios.post('/api/chat', { message: message, stage: stage })
            .then(function(response) {
                var data = response.data;
                var chatBox = document.getElementById('chat-box');
                if (data.mode && data.mode !== "default") {
                    chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> ' + data.response + '</div>';
                    displaySelectionOptions(data.options);
                    currentStage = data.mode;
                } else {
                    chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> ' + data.response + '</div>';
                    currentStage = "default";
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            })
            .catch(function(error) {
                console.error('Error:', error);
                var chatBox = document.getElementById('chat-box');
                chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> エラーが発生しました。</div>';
            });
    }

    function displaySelectionOptions(options) {
        var selectionBox = document.getElementById('selection-box');
        selectionBox.innerHTML = '';
        options.forEach(function(option) {
            var btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.innerText = option;
            btn.onclick = function() { sendMessageFromOption(option); };
            selectionBox.appendChild(btn);
        });
        selectionBox.style.display = 'block';
    }

    function sendMessageFromOption(option) {
        var chatBox = document.getElementById('chat-box');
        chatBox.innerHTML += '<div class="message"><strong>あなた:</strong> ' + option + '</div>';
        document.getElementById('selection-box').innerHTML = '';
        document.getElementById('selection-box').style.display = 'none';
        sendToAPI(option, currentStage);
    }
</script>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(function(registration) {
                console.log('Service Worker registered with scope:', registration.scope);
            })
            .catch(function(error) {
                console.error('Service Worker registration failed:', error);
            });
    }
</script>
<script src="/js/sync.js"></script>
</body>
</html>
