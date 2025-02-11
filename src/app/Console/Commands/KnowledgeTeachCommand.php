<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Knowledge;
use App\Models\Category;

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
    protected $description = '対話形式で Techtinic に知識を教える';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Techtinic に知識を教えましょう。");

        // 1. カテゴリーの選択
        $this->info("まず、知識のカテゴリーを選択してください。");
        $existingCategories = Category::all();

        if ($existingCategories->isEmpty()) {
            $this->info("現在、登録されているカテゴリーはありません。");
            $categoryName = $this->ask("新しいカテゴリー名を入力してください:");
            $category = Category::create(['name' => $categoryName]);
        } else {
            // カテゴリー一覧の表示と選択肢に「新規」を追加
            $choices = $existingCategories->pluck('name')->toArray();
            $choices[] = '新規';

            $choice = $this->choice("知識のカテゴリーを選択してください（新規作成する場合は '新規' を選んでください）", $choices, 0);

            if ($choice === '新規') {
                $newCategoryName = $this->ask("新しいカテゴリー名を入力してください:");
                $category = Category::create(['name' => $newCategoryName]);
            } else {
                $category = Category::where('name', $choice)->first();
            }
        }

        // 2. タイトルの入力
        $title = $this->ask("知識のタイトルを入力してください:");

        // 3. 内容の入力
        $content = $this->ask("その内容を入力してください:");

        // 4. 知識の保存
        $knowledge = Knowledge::create([
            'title'   => $title,
            'content' => $content,
        ]);

        // 5. カテゴリーとの関連付け（多対多）
        $knowledge->categories()->attach($category->id);

        $this->info("「{$title}」の知識が、カテゴリー「{$category->name}」として保存されました！");

        return 0;
    }
}
