<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatKnowledgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. タグのテストデータを挿入
        $tags = [
            ['name' => 'Laravel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PHP',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Database','created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('chat_tags')->insert($tags);

        // 2. カテゴリのテストデータを挿入
        $categories = [
            ['name' => 'Backend', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'API',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DevOps',  'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('chat_categories')->insert($categories);

        // 3. 知識（chat_knowledge）のテストデータを3件挿入
        $knowledges = [
            [
                'title'      => 'Laravel Migration Tips',
                'content'    => 'This is a guide on how to create and manage migrations in Laravel.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'PHP Artisan Commands',
                'content'    => 'Learn how to use various Artisan commands to streamline your workflow.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Database Optimization',
                'content'    => 'Best practices for optimizing your database queries and indexing.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('chat_knowledge')->insert($knowledges);

        // 4. 挿入したデータのIDを取得
        $knowledgeEntries = DB::table('chat_knowledge')->get();
        $tagEntries       = DB::table('chat_tags')->get();
        $categoryEntries  = DB::table('chat_categories')->get();

        // 5. 各知識エントリに対して、最初のタグと最初のカテゴリーを紐付け（pivotテーブルへ）
        foreach ($knowledgeEntries as $knowledge) {
            DB::table('chat_knowledge_tag')->insert([
                'knowledge_id' => $knowledge->id,
                'tag_id'       => $tagEntries->first()->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            DB::table('chat_knowledge_category')->insert([
                'knowledge_id' => $knowledge->id,
                'category_id'  => $categoryEntries->first()->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
