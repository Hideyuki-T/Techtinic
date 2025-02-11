<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Knowledge;

class KnowledgeTeachCommand extends Command
{
    protected $signature = 'knowledge:teach';
    protected $description = '対話形式でTechtinicに知識を教える';

    public function handle()
    {
        $title = $this->ask('知識のブランチ名称を入力してください');
        $content = $this->ask('その内容を入力してください');

        Knowledge::create([
            'title' => $title,
            'content' => $content,
        ]);

        $this->info("「{$title}」覚えたよ！");
        return 0;
    }
}
