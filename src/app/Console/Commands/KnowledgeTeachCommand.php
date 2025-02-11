<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Knowledge;
use App\Models\Category;
use App\Models\Tag;

class KnowledgeTeachCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knowledge:teach';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '対話形式でTechtinicに知識を教える';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Techtinic に知識を教えましょう。");

        // 1. カテゴリーの選択または作成
        $this->info("まず、知識のカテゴリーを選択してください。");
        $existingCategories = Category::all();
        if ($existingCategories->isEmpty()) {
            $this->info("現在、登録されているカテゴリーはありません。");
            $categoryName = $this->ask("新しいカテゴリー名を入力してください:");
            $category = Category::create(['name' => $categoryName]);
        } else {
            $choices = $existingCategories->pluck('name')->toArray();
            $choices[] = '新規';
            $selectedCategoryName = $this->choice("知識のカテゴリーを選択してください（新規作成する場合は '新規' を選んでください）", $choices, 0);
            if ($selectedCategoryName === '新規') {
                $newCategoryName = $this->ask("新しいカテゴリー名を入力してください:");
                $category = Category::create(['name' => $newCategoryName]);
            } else {
                $category = Category::where('name', $selectedCategoryName)->first();
            }
        }

        // 2. タイトルの入力
        $title = $this->ask("知識のタイトルを入力してください:");

        // 3. 本文の入力
        $content = $this->ask("その内容を入力してください:");

        // 4. 知識の保存
        $knowledge = Knowledge::create([
            'title'   => $title,
            'content' => $content,
        ]);

        // カテゴリーとの関連付け
        $knowledge->categories()->attach($category->id);

        // 5. タグの入力
        $tagInput = $this->ask("この知識に関連するタグ（カンマ区切り）を入力してください (未入力なら、カテゴリー名をタグとして登録します):");
        if (empty(trim($tagInput))) {
            $tagInput = $category->name;
        }
        $tagNames = array_map('trim', explode(',', $tagInput));
        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                // 既存のタグを検索、なければ新規作成
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                // タグと知識の関連付け
                $knowledge->tags()->attach($tag->id);
            }
        }

        $this->info("「{$title}」の知識が、カテゴリー「{$category->name}」およびタグとして登録されました！");

        return 0;
    }
}
