<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お気に入り一覧</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#AAF0D1">
    <style>
        body { font-family: Arial, sans-serif; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 10px; }
        .fav-item { padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
<h1>お気に入り一覧</h1>
<!-- 登録済みURLの一覧 -->
<ul id="urlList">
    <!-- サンプルデータ -->
    <li class="fav-item">
        <strong>カテゴリー:</strong> Qiita<br>
        <strong>タイトル:</strong> Qiitaのスクレイピング記事<br>
        <strong>URL:</strong> <a href="https://qiita.com/yastinbieber/items/d296eae25a5b487ce3fb" target="_blank">https://qiita.com/...</a><br>
        <strong>タグ:</strong> JavaScript, スクレイピング
    </li>
    <!-- ここにさらに登録済みデータを追加できます -->
</ul>

<!-- 登録ページへの遷移ボタン -->
<button id="registerBtn">登録</button>

<script>
    document.getElementById('registerBtn').addEventListener('click', function() {
        // 登録ページへリダイレクト
        window.location.href = '/register.html';
    });
</script>
</body>
</html>
