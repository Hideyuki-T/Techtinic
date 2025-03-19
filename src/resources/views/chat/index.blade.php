<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャットデータの表示</title>
</head>
<body>
<h1>チャットデータ</h1>
<pre id="output"></pre>

<script>
    fetch('/api/chat/data')
        .then(response => response.json())
        .then(data => {
            document.getElementById('output').textContent = JSON.stringify(data, null, 2);
        })
        .catch(error => console.error('データ取得エラー:', error));
</script>
</body>
</html>
