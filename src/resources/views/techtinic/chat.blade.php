<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techtinic Chat</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- axiosライブラリの読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- その他必要なスクリプト等 -->
    <link rel="manifest" href="/manifest.json">
</head>
<body>
<!-- 例: チャット画面の上部にボタンを追加 -->
<div style="margin-bottom: 10px;">
    <a href="/teach" class="btn">知識登録画面へ</a>
</div>
<h1>Techtinic Chat</h1>
<div id="chat-box">
    <!-- チャット履歴がここに表示される -->
</div>
<!-- 選択肢を表示する領域 -->
<div id="selection-box"></div>
<input type="text" id="message" placeholder="メッセージを入力" style="width: 70%;">
<button onclick="sendMessage()">送信</button>

<script>
    // グローバル変数で対話のステージを管理。初期は "default"。
    var currentStage = "default";

    // ユーザーのメッセージを送信し、APIからの応答を処理する関数
    function sendMessage() {
        var messageInput = document.getElementById('message');
        var message = messageInput.value.trim();
        if (message === '') return;

        var chatBox = document.getElementById('chat-box');
        // ユーザーの発言をチャットボックスに追加
        chatBox.innerHTML += '<div class="message"><strong>あなた:</strong> ' + message + '</div>';

        // 入力欄をクリア
        messageInput.value = '';
        // 選択肢エリアを一旦クリアして非表示にする
        document.getElementById('selection-box').innerHTML = '';
        document.getElementById('selection-box').style.display = 'none';

        // 現在のステージを添えてAPIに送信
        sendToAPI(message, currentStage);
    }

    // APIへのPOSTリクエストを行い、応答を処理する関数
    function sendToAPI(message, stage) {
        axios.post('/api/chat', { message: message, stage: stage })
            .then(function(response) {
                var data = response.data;
                var chatBox = document.getElementById('chat-box');

                // レスポンスに "mode" が設定され、"default" 以外の場合は対話モードが切り替わったと判断
                if (data.mode && data.mode !== "default") {
                    // 応答メッセージを表示
                    chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> ' + data.response + '</div>';
                    // 選択肢を表示
                    displaySelectionOptions(data.options);
                    // 現在のステージを更新
                    currentStage = data.mode;
                } else {
                    // 通常の応答の場合
                    chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> ' + data.response + '</div>';
                    // 応答後はステージをリセット
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

    // 選択肢を表示する関数
    function displaySelectionOptions(options) {
        var selectionBox = document.getElementById('selection-box');
        selectionBox.innerHTML = '';  // クリア

        options.forEach(function(option) {
            var btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.innerText = option;
            btn.onclick = function() {
                // 選択肢が選ばれたら、その内容を新たなメッセージとして送信する
                sendMessageFromOption(option);
            };
            selectionBox.appendChild(btn);
        });
        selectionBox.style.display = 'block';
    }

    // 選択肢が選ばれた場合にその内容を送信する関数
    function sendMessageFromOption(option) {
        var chatBox = document.getElementById('chat-box');
        chatBox.innerHTML += '<div class="message"><strong>あなた:</strong> ' + option + '</div>';

        // 選択肢エリアを非表示にする
        document.getElementById('selection-box').innerHTML = '';
        document.getElementById('selection-box').style.display = 'none';

        // 現在のステージを維持して選択肢の内容を送信（サーバー側で stage によって処理が分岐する）
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
</body>
</html>
