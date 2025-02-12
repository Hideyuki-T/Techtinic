<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Category;
use App\Models\Knowledge;

class ChatService
{
    /**
     * ユーザーの入力に応じた対話処理を行い、結果を配列で返す
     *
     * @param string $input ユーザー入力
     * @return array 対話結果（'response'、'mode'、必要に応じて 'options' や 'content' を含む）
     */
    public function processMessage(string $input): array
    {
        // 入力が「どんなことを知ってる？」の場合
        if (trim($input) === "どんなことを知ってる？") {
            // タグ一覧を取得
            $tags = Tag::all();
            if ($tags->isEmpty()) {
                // タグがなければカテゴリー一覧を返す
                $categories = Category::all();
                if ($categories->isEmpty()) {
                    return [
                        'response' => "現在、何も教えてないみたいだね。これから沢山教えてね！",
                        'mode' => 'default'
                    ];
                }
                return [
                    'response' => "タグはまだ登録されていないみたいです。代わりに、以下のカテゴリーが登録されています:",
                    'mode' => 'category_selection',
                    'options' => $categories->pluck('name')->toArray()
                ];
            }
            return [
                'response' => "こんなタグを記憶してるよ。どれが気になるかな？",
                'mode' => 'tag_selected',
                'options' => $tags->pluck('name')->toArray()
            ];
        }

        // もし現在のステージが「tag_selected」なら、入力はタグとして扱う
        // ※ このあたりは、クライアント側でステージ管理し、次の入力がタグ選択後のものとして送られてくることを前提とする
        $selectedTag = Tag::whereRaw('lower(name) = ?', [strtolower($input)])->first();
        if ($selectedTag) {
            $knowledgeItems = $selectedTag->knowledges()->get();
            if ($knowledgeItems->isEmpty()) {
                return [
                    'response' => "そのタグに関連する知識は見つかりませんでした。",
                    'mode' => 'default'
                ];
            }
            return [
                'response' => "このタグだとこんなことについて知ってるよ。どれか気になる？",
                'mode' => 'title_selected',
                'options' => $knowledgeItems->pluck('title')->toArray()
            ];
        }

        // もし入力が知識のタイトルとして選ばれた場合
        $knowledge = Knowledge::where('title', $input)->first();
        if ($knowledge) {
            return [
                'response' => "選択された知識「{$knowledge->title}」の内容は以下です:\n" . $knowledge->content,
                'mode' => 'default'
            ];
        }

        // 通常はAIEngine等による検索結果（ここではデフォルトの応答）
        return [
            'response' => "申し訳ありません、その知識はまだ教えられていません。",
            'mode' => 'default'
        ];
    }
}
