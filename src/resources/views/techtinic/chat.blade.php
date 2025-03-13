@extends('layouts.app')

@section('title', 'Techtinic Chat')

@section('content')
    <div id="sw-status">Registering Service Worker...</div>

    <!-- オフライン通知バナー -->
    <div id="offline-banner" style="display: none; background: #ffcccc; color: #333; padding: 10px; text-align: center;">
        現在、オフライン状態です。最新のデータは取得できません。
    </div>

    <!-- 上部のリンク -->
    <div style="margin-bottom: 10px;">
        <a href="/teach" class="btn">Knowledge Registration</a>
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
        <a href="/knowledge" class="btn">IndexedDB</a>
    </p>
@endsection

@section('scripts')
    <script>
        // axios のタイムアウト設定など、チャット画面専用の JavaScript コードを記述
        axios.defaults.timeout = 5000;
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
                    chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> エラーが発生しましたよ。</div>';
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
@endsection
