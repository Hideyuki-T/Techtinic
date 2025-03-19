<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>チャットデータ表示</title>
    <style>
        table, th, td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<h1>チャットデータ表示</h1>

<h2>チャットタグ (chat_tags)</h2>
<table id="tags-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>タグ名</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<h2>チャットカテゴリー (chat_categories)</h2>
<table id="categories-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>カテゴリー名</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<h2>チャット知識 (chat_knowledge)</h2>
<table id="knowledges-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>タイトル</th>
        <th>内容</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<h2>チャット知識タグ (chat_knowledge_tag)</h2>
<table id="knowledgeTags-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>知識ID</th>
        <th>タグID</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<h2>チャット知識カテゴリー (chat_knowledge_category)</h2>
<table id="knowledgeCategories-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>知識ID</th>
        <th>カテゴリーID</th>
        <th>作成日時</th>
        <th>更新日時</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    // /chat-data エンドポイントからJSONデータを取得し、各テーブルに出力する
    fetch('/chat-data')
        .then(response => response.json())
        .then(data => {
            // チャットタグ
            const tagsBody = document.querySelector('#tags-table tbody');
            data.tags.forEach(tag => {
                const row = `
                        <tr>
                            <td>${tag.id}</td>
                            <td>${tag.name}</td>
                            <td>${tag.created_at ?? ''}</td>
                            <td>${tag.updated_at ?? ''}</td>
                        </tr>`;
                tagsBody.innerHTML += row;
            });

            // チャットカテゴリー
            const categoriesBody = document.querySelector('#categories-table tbody');
            data.categories.forEach(category => {
                const row = `
                        <tr>
                            <td>${category.id}</td>
                            <td>${category.name}</td>
                            <td>${category.created_at ?? ''}</td>
                            <td>${category.updated_at ?? ''}</td>
                        </tr>`;
                categoriesBody.innerHTML += row;
            });

            // チャット知識
            const knowledgesBody = document.querySelector('#knowledges-table tbody');
            data.knowledges.forEach(knowledge => {
                const row = `
                        <tr>
                            <td>${knowledge.id}</td>
                            <td>${knowledge.title}</td>
                            <td>${knowledge.content}</td>
                            <td>${knowledge.created_at ?? ''}</td>
                            <td>${knowledge.updated_at ?? ''}</td>
                        </tr>`;
                knowledgesBody.innerHTML += row;
            });

            // チャット知識タグ
            const knowledgeTagsBody = document.querySelector('#knowledgeTags-table tbody');
            data.knowledgeTags.forEach(kt => {
                const row = `
                        <tr>
                            <td>${kt.id}</td>
                            <td>${kt.knowledge_id}</td>
                            <td>${kt.tag_id}</td>
                            <td>${kt.created_at ?? ''}</td>
                            <td>${kt.updated_at ?? ''}</td>
                        </tr>`;
                knowledgeTagsBody.innerHTML += row;
            });

            // チャット知識カテゴリー
            const knowledgeCategoriesBody = document.querySelector('#knowledgeCategories-table tbody');
            data.knowledgeCategories.forEach(kc => {
                const row = `
                        <tr>
                            <td>${kc.id}</td>
                            <td>${kc.knowledge_id}</td>
                            <td>${kc.category_id}</td>
                            <td>${kc.created_at ?? ''}</td>
                            <td>${kc.updated_at ?? ''}</td>
                        </tr>`;
                knowledgeCategoriesBody.innerHTML += row;
            });
        })
        .catch(error => {
            console.error('データ取得エラー:', error);
        });
</script>
</body>
</html>
