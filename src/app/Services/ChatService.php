<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Category;
use App\Models\Knowledge;

class ChatService
{
    /**
     * ユーザーの入力に応じた対話処理を行い、結果を配列で返す
     */
    public function processMessage(string $input): array
    {
        // ここに既存のロジックをそのまま記述
        if (trim($input) === "どんなことを知ってる？") {
            $tags = Tag::all();
            if ($tags->isEmpty()) {
                $categories = Category::all();
                if ($categories->isEmpty()) {
                    return [
                        'response' => "今はまだ何も知らないんだ。。。これから沢山教えてね！",
                        'mode' => 'default'
                    ];
                }
                return [
                    'response' => "タグはまだ登録されていないみたいだよ。代わりに、以下のカテゴリーが登録されてるよ：",
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

        $selectedTag = Tag::whereRaw('lower(name) = ?', [strtolower($input)])->first();
        if ($selectedTag) {
            $knowledgeItems = $selectedTag->knowledges()->get();
            if ($knowledgeItems->isEmpty()) {
                return [
                    'response' => "そのタグに関連することは知らないなぁ。。。",
                    'mode' => 'default'
                ];
            }
            return [
                'response' => "このタグだとこんなことについて知ってるよ。どれか気になる？",
                'mode' => 'title_selected',
                'options' => $knowledgeItems->pluck('title')->toArray()
            ];
        }

        $knowledge = Knowledge::where('title', $input)->first();
        if ($knowledge) {
            return [
                'response' => "確か...「{$knowledge->title}」の内容はこうだったよ！\n" . $knowledge->content,
                'mode' => 'default'
            ];
        }

        return [
            'response' => "申し訳ありません、その知識はまだ教えられていません。",
            'mode' => 'default'
        ];
    }

    /**
     * 知識情報を取得するためのヘルパーメソッド
     */
    public function processKnowledge(): array
    {
        return $this->processMessage("どんなことを知ってる？");
    }
}
