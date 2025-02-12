<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techtinic 知識登録</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"],
        textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 20px; font-size: 16px; }
        .alert { padding: 10px; background-color: #dff0d8; color: #3c763d; margin-bottom: 20px; }
    </style>
</head>
<body>
<h1>Techtinic に知識を教える</h1>

@if(session('success'))
    <div class="alert">
        {{ session('success') }}
    </div>
@endif

<form action="/knowledge/teach" method="POST">
    @csrf
    <div class="form-group">
        <label for="category">カテゴリー</label>
        <!-- ここでは自由入力にしていますが、必要ならドロップダウンで既存カテゴリーを選択できるように工夫も可能 -->
        <input type="text" name="category" id="category" placeholder="例: dockerコマンド" required>
    </div>
    <div class="form-group">
        <label for="title">タイトル</label>
        <input type="text" name="title" id="title" placeholder="例: 起動済のコンテナ一覧の表示" required>
    </div>
    <div class="form-group">
        <label for="content">本文</label>
        <textarea name="content" id="content" rows="4" placeholder="例: docker ps と入力して、起動中のコンテナ一覧を表示する" required></textarea>
    </div>
    <div class="form-group">
        <label for="tags">タグ (カンマ区切り)</label>
        <input type="text" name="tags" id="tags" placeholder="例: docker, コンテナ, 状態確認">
    </div>
    <button type="submit">知識を登録する</button>
</form>
</body>
</html>
