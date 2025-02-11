<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIEngine;
use App\Models\Knowledge;
use App\Models\Tag;

class ChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CLI上でTechtinicと会話する';

    protected $aiEngine;

    /**
     * Create a new command instance.
     *
     * @param AIEngine $aiEngine
     */
    public function __construct(AIEngine $aiEngine)
    {
        parent::__construct();
        $this->aiEngine = $aiEngine;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("それじゃあ話そうか。やめたくなったら 'exit' と入力してね。");

        while (true) {
            $input = $this->ask('あなた');
            if (trim($input) === 'exit') {
                $this->info('またね。');
                break;
            }

            // 特定の入力で知識検索ルートを呼び出す
            if (trim($input) === "どんなことを知ってる？") {
                $this->handleKnowledgeQuery();
                continue;
            }

            // 通常の対話処理（AIEngine の回答）
            $response = $this->aiEngine->getResponse($input);
            $this->info("Techtinic: {$response}");
        }

        return 0;
    }

    /**
     * 対話形式で知識検索を行う処理（タグによる検索）
     */
    protected function handleKnowledgeQuery()
    {
        // まず、タグ一覧を取得
        $tags = Tag::all();

        // タグがない場合は、代わりにカテゴリー一覧を表示する
        if ($tags->isEmpty()) {
            $this->info("タグはまだ登録されていないみたいです。代わりに、以下のカテゴリーが登録されています:");
            $categories = \App\Models\Category::all();
            if ($categories->isEmpty()) {
                $this->info("現在、何も教えてないみたいだね。これから沢山教えてね！");
                return;
            }
            $categoryChoices = $categories->pluck('name')->toArray();
            $selectedCategoryName = $this->choice("どのカテゴリーに興味がありますか？", $categoryChoices);
            $selectedCategory = \App\Models\Category::where('name', $selectedCategoryName)->first();
            $knowledgeItems = $selectedCategory->knowledges()->get();
        } else {
            // タグが存在する場合は、タグ一覧から選択
            $tagChoices = $tags->pluck('name')->toArray();
            $selectedTagName = $this->choice("こんなタグを記憶してるよ。どれが気になるかな？", $tagChoices);
            $selectedTag = Tag::where('name', $selectedTagName)->first();
            $knowledgeItems = $selectedTag->knowledges()->get();
        }

        if ($knowledgeItems->isEmpty()) {
            $this->info("選択された分類に関連する知識は見つかりませんでした。");
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
    }
}
