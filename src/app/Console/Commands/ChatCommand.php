<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Knowledge;
use App\Services\AIEngine;
use App\Models\Tag;

class ChatCommand extends Command
{
    protected $signature = 'chat:run';
    protected $description = 'CLI上でTechtinicと会話する';
    protected $aiEngine;
    private $currentStage = 'default';

    public function __construct(AIEngine $aiEngine)
    {
        parent::__construct();
        $this->aiEngine = $aiEngine;
    }

    public function handle()
    {
        $this->info("それじゃあ話そうか。やめたくなったら 'exit' と入力してね。");

        while (true) {
            $input = $this->ask('あなた');
            if (trim($input) === 'exit') {
                $this->info('またね。');
                break;
            }

            // 選択肢が表示されている場合、「どんなことを知ってる？」の再入力を防ぐ
            if (trim($input) === "どんなことを知ってる？") {
                if ($this->currentStage !== 'default') {
                    $this->info("すでに選択肢が表示されています。表示された中から選んでください。");
                    continue;
                }
                $this->handleKnowledgeQuery();
                continue;
            }

            // 通常の対話処理（AIEngine の回答）
            $response = $this->aiEngine->getResponse($input);
            $this->info("Techtinic: {$response}");
        }

        return 0;
    }

    protected function handleKnowledgeQuery()
    {
        // ここで対話ステージを更新
        $this->currentStage = 'tag_selected';

        // タグ一覧を取得
        $tags = Tag::all();

        if ($tags->isEmpty()) {
            $this->info("タグはまだ登録されていないみたいです。代わりに、以下のカテゴリーが登録されています:");
            $categories = \App\Models\Category::all();
            if ($categories->isEmpty()) {
                $this->info("現在、何も教えてないみたいだね。これから沢山教えてね！");
                $this->currentStage = 'default';
                return;
            }
            $categoryChoices = $categories->pluck('name')->toArray();
            $selectedCategoryName = $this->choice("どのカテゴリーに興味がありますか？", $categoryChoices);
            $selectedCategory = \App\Models\Category::where('name', $selectedCategoryName)->first();
            $knowledgeItems = $selectedCategory->knowledges()->get();
        } else {
            $tagChoices = $tags->pluck('name')->toArray();
            $selectedTagName = $this->choice("こんなタグを記憶してるよ。どれが気になるかな？", $tagChoices);
            // ケースインセンシティブ検索
            $selectedTag = Tag::whereRaw('lower(name) = ?', [strtolower($selectedTagName)])->first();
            $knowledgeItems = $selectedTag->knowledges()->get();
        }

        if ($knowledgeItems->isEmpty()) {
            $this->info("選択された分類に関連する知識は見つかりませんでした。");
            $this->currentStage = 'default';
            return;
        }

        $knowledgeChoices = $knowledgeItems->pluck('title')->toArray();
        $selectedTitle = $this->choice("この中でどれが気になる？", $knowledgeChoices);
        $knowledge = Knowledge::where('title', $selectedTitle)->first();

        if ($knowledge) {
            $this->info("選択された知識「{$knowledge->title}」の内容は以下です:");
            $this->line($knowledge->content);
        } else {
            $this->error("選択された知識が見つかりませんでした。");
        }
        // 対話終了時にステージをリセット
        $this->currentStage = 'default';
    }
}
