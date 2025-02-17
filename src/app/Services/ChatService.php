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
     * CLI環境の場合は対話ループの中で呼び出され、
     * Web環境の場合は1回分の処理のみを行い即結果を返す
     *
     * @param string $input ユーザー入力
     * @return array 対話結果（'response'、'mode'、必要に応じて 'options' や 'content' を含む）
     */
    public function processMessage(string $input): array
    {
        if (app()->runningInConsole()) {
            // CLI環境では既存の対話処理（たとえば ChatCommand 内でループしている前提）
            return $this->processInteractiveMessage($input);
        } else {
            // Web環境では1回分の処理のみ実行して即結果を返す
            return $this->processSingleTurnMessage($input);
        }
    }

    /**
     * CLI 用の対話処理（従来の動作）
     */
    protected function processInteractiveMessage(string $input): array
    {
        // ここで、CLI用の対話ループ中に呼ばれる処理があれば記述する
        // 例として、単純に共通ロジックを呼び出す場合は以下のようにする
        return $this->processMessageLogic($input);
    }

    /**
     * Web 用の1回分の処理
     */
    protected function processSingleTurnMessage(string $input): array
    {
        // 対話ループなどは行わず、入力に応じた処理結果を即座に返す
        return $this->processMessageLogic($input);
    }

    /**
     * CLI/Web 共通のメッセージ処理ロジック
     */
    protected function processMessageLogic(string $input): array
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
                        'response' => "今はまだ何も知らないんだ。。。これから沢山教えてね！",
                        'mode' => 'default'
                    ];
                }
                return [
                    'response' => "タグはまだ登録されていないみたいだよ。代わりに、以下のカテゴリーが登録されてるよ。:",
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

        // もし入力が知識のタイトルとして選ばれた場合
        $knowledge = Knowledge::where('title', $input)->first();
        if ($knowledge) {
            return [
                'response' => "確か...「{$knowledge->title}」の内容はこうだったよ！\n" . $knowledge->content,
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
