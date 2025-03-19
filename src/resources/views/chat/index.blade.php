<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>チャットデータ確認</title>
    <style>
        table, th, td {
            border: 1px solid #ddd;
            border-collapse: collapse;
            padding: 8px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        h1 {
            margin-top: 40px;
        }
    </style>
</head>
<body>
<h1>チャットタグ (chat_tags)</h1>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>タグ名</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tags as $tag)
        <tr>
            <td>{{ $tag->id }}</td>
            <td>{{ $tag->name }}</td>
            <td>{{ $tag->created_at }}</td>
            <td>{{ $tag->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h1>チャットカテゴリー (chat_categories)</h1>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>カテゴリー名</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody>
    @foreach($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->created_at }}</td>
            <td>{{ $category->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h1>チャット知識 (chat_knowledge)</h1>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>タイトル</th>
        <th>内容</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody>
    @foreach($knowledges as $knowledge)
        <tr>
            <td>{{ $knowledge->id }}</td>
            <td>{{ $knowledge->title }}</td>
            <td>{{ $knowledge->content }}</td>
            <td>{{ $knowledge->created_at }}</td>
            <td>{{ $knowledge->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h1>チャット知識タグ (chat_knowledge_tag)</h1>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>知識ID</th>
        <th>タグID</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody>
    @foreach($knowledgeTags as $kt)
        <tr>
            <td>{{ $kt->id }}</td>
            <td>{{ $kt->knowledge_id }}</td>
            <td>{{ $kt->tag_id }}</td>
            <td>{{ $kt->created_at }}</td>
            <td>{{ $kt->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h1>チャット知識カテゴリー (chat_knowledge_category)</h1>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>知識ID</th>
        <th>カテゴリーID</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody>
    @foreach($knowledgeCategories as $kc)
        <tr>
            <td>{{ $kc->id }}</td>
            <td>{{ $kc->knowledge_id }}</td>
            <td>{{ $kc->category_id }}</td>
            <td>{{ $kc->created_at }}</td>
            <td>{{ $kc->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
