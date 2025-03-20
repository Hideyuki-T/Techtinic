<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Laravel Tetris</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* シンプルなスタイル例 */
        body { font-family: Arial, sans-serif; }
        #game { width: 300px; height: 600px; background: #eee; margin: 20px auto; border: 2px solid #333; }
    </style>
</head>
<body>
<h1>テトリス</h1>
<div id="game">
    <!-- ゲームエリア。JavaScriptでテトリスロジックを実装する -->
</div>
<div id="score-area">
    <input type="text" id="player_name" placeholder="プレイヤー名">
    <button id="submit-score">スコアを送信</button>
</div>

<script>
    // CSRFトークンのセットアップ
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ここにテトリスのJavaScriptロジックを実装
    // ※ゲームオーバー時にscoreをPOSTで送信する例

    document.getElementById('submit-score').addEventListener('click', function() {
        const playerName = document.getElementById('player_name').value;
        // 仮のスコア。実際はゲーム内で算出する
        const score = Math.floor(Math.random() * 1000);

        fetch('/tetris/score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ player_name: playerName, score: score })
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
</script>
</body>
</html>
