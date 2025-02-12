<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techtinic 知識登録</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- その他必要なスクリプト等 -->
</head>
<body>
<h1>Techtinic に知識を教える</h1>

@if(session('success'))
    <div class="alert">
        {{ session('success') }}
    </div>
@endif

<form action="/teach" method="POST">
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
