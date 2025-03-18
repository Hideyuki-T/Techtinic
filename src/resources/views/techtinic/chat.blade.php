@extends('layouts.app')

@section('title', 'Techtinic Chat')

@section('content')
    <div id="sw-status">Registering Service Worker...</div>

    <!-- オフライン通知バナー -->
    <div id="offline-banner" style="display: none; background: #ffcccc; color: #333; padding: 10px; text-align: center;">
        現在、オフライン状態です。最新のデータは取得できません。
    </div>
    <h1>Chat T</h1>
    <div id="chat-box">
        <!-- チャット履歴がここに表示される -->
    </div>
    <div id="selection-box"></div>
    <input type="text" id="message" placeholder="メッセージを入力" style="width: 70%;">
    <button onclick="sendMessage()">送信</button>

    <!-- 各ページへのリンク -->
    <p style="margin-top:20px;">
        <a href="/teach" class="btn Knowledge-btn">Knowledge Registration</a>
        <a href="/knowledge" class="btn IndexedDB-btn">IndexedDB</a>
        <a href="/main" class="btn return-btn">return to main</a>
    </p>
@endsection

@section('scripts')
    <script>
        // axios は使用せず fetch を使うため、axios の設定は削除
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

        // sendToAPI を async/await で実装
        async function sendToAPI(message, stage) {
            try {
                const response = await fetch('/api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message, stage: stage })
                });
                if (!response.ok) {
                    throw new Error('ネットワークエラー: ' + response.status);
                }
                const data = await response.json();
                var chatBox = document.getElementById('chat-box');
                if (data.offline) {
                    chatBox.innerHTML += '<div class="message"><strong>Techtinic (Offline):</strong> ' + data.response + '</div>';
                    if (data.messages && data.messages.length > 0) {
                        chatBox.innerHTML += '<div class="message"><strong>Offline History:</strong></div>';
                        data.messages.forEach(function(msg) {
                            chatBox.innerHTML += '<div class="message"><strong>' + new Date(msg.timestamp).toLocaleTimeString() + ':</strong> ' + msg.message + '</div>';
                        });
                    }
                    if (data.mode && data.mode !== "default") {
                        displaySelectionOptions(data.options);
                        currentStage = data.mode;
                    } else {
                        currentStage = "default";
                    }
                } else {
                    if (data.mode && data.mode !== "default") {
                        chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> ' + data.response + '</div>';
                        displaySelectionOptions(data.options);
                        currentStage = data.mode;
                    } else {
                        chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> ' + data.response + '</div>';
                        currentStage = "default";
                    }
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            } catch (error) {
                console.error("sendToAPI エラー:", error);
                // ネットワークエラー時は fallbackChatMessage を呼び出す
                fallbackChatMessage(message);
            }
        }
        window.sendToAPI = sendToAPI;

        // オフライン時のフォールバック処理：
        // IndexedDB を利用して、送信メッセージを保存し、過去のオフラインチャット履歴を表示する
        async function fallbackChatMessage(message) {
            console.log("オフラインチャット処理:", message);
            try {
                // IndexedDB へ接続。window.idb は sync.js で公開済み。
                const db = await window.idb.openDB('techtinic-db', 1, {
                    upgrade(db) {
                        if (!db.objectStoreNames.contains('chatMessages')) {
                            db.createObjectStore('chatMessages', { keyPath: 'id', autoIncrement: true });
                        }
                    }
                });
                // 送信メッセージを保存
                const tx = db.transaction('chatMessages', 'readwrite');
                const store = tx.objectStore('chatMessages');
                const chatMessage = {
                    message: message,
                    timestamp: Date.now()
                };
                await store.add(chatMessage);
                await tx.done;
                console.log("チャットメッセージを IndexedDB に保存しました。", chatMessage);

                // IndexedDB から全てのチャットメッセージを取得
                const tx2 = db.transaction('chatMessages', 'readonly');
                const store2 = tx2.objectStore('chatMessages');
                const messages = await store2.getAll();
                var chatBox = document.getElementById('chat-box');
                chatBox.innerHTML += '<div class="message"><strong>Techtinic (Offline):</strong> オフライン状態です。メッセージは保存されました。</div>';
                if (messages.length > 0) {
                    chatBox.innerHTML += '<div class="message"><strong>Offline History:</strong></div>';
                    messages.forEach(function(msg) {
                        chatBox.innerHTML += '<div class="message"><strong>' + new Date(msg.timestamp).toLocaleTimeString() + ':</strong> ' + msg.message + '</div>';
                    });
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            } catch (error) {
                console.error("fallbackChatMessage IndexedDB エラー:", error);
                var chatBox = document.getElementById('chat-box');
                chatBox.innerHTML += '<div class="message"><strong>Techtinic:</strong> オフライン状態です...チャット履歴を取得できません。</div>';
            }
        }
        window.fallbackChatMessage = fallbackChatMessage;

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
