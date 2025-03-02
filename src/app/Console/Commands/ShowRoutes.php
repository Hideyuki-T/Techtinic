<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Illuminate\Support\Facades\Route;

class ShowRoutes extends Command
{
    // コマンド名とオプションの定義（--custom を指定するとカスタムカラー出力）
    //デフォ：php artisan show:routes　　カスタムカラー：php artisan show:routes --custom
    protected $signature = 'show:routes {--custom : Use custom colors for output}';

    protected $description = 'Display the list of routes with their actions, with an option for custom colors';

    public function handle()
    {
        $routes = Route::getRoutes();

        // オプション --custom が指定されている場合は、カスタムカラーを設定
        if ($this->option('custom')) {
            // 例: 文字色：白、背景色：青
            $style = new OutputFormatterStyle('white', 'blue');
            $this->output->getFormatter()->setStyle('custom', $style);

            foreach ($routes as $route) {
                $this->line("<custom>Method: " . implode(',', $route->methods()) . "</custom>");
                $this->line("<custom>Path: " . $route->uri() . "</custom>");
                $this->line("<custom>Action: " . $route->getActionName() . "</custom>");
                $this->line("<custom>--------------------------------------</custom>");
            }
        } else {
            // デフォルトの色出力
            foreach ($routes as $route) {
                $this->info("Method: " . implode(',', $route->methods()));
                $this->info("Path: " . $route->uri());
                $this->info("Action: " . $route->getActionName());
                $this->info("--------------------------------------");
            }
        }
    }
}
