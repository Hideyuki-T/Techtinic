<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CustomDownCommand extends Command
{
    // 引数としてマイグレーションファイル名を受け取る
    protected $signature = 'db:custom-down {file : マイグレーションファイル名 (ファイル名全量を記述すること)}';

    protected $description = '指定したマイグレーションファイルの down メソッドを実行する';

    public function handle()
    {
        $file = $this->argument('file');
        $migrationPath = database_path('migrations') . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($migrationPath)) {
            $this->error("指定されたマイグレーションファイルが存在しません: {$file}");
            return 1;
        }

        // マイグレーションファイル読み込み前のクラス一覧を取得
        $declaredClassesBefore = get_declared_classes();

        // マイグレーションファイルを読み込む
        require_once $migrationPath;

        // 読み込み後のクラス一覧を取得し、読み込んだ新規クラスを特定
        $declaredClassesAfter = get_declared_classes();
        $newClasses = array_diff($declaredClassesAfter, $declaredClassesBefore);

        // Illuminate\Database\Migrations\Migration を継承し、down メソッドを持つクラスを探索
        $migrationClass = null;
        foreach ($newClasses as $class) {
            if (is_subclass_of($class, 'Illuminate\Database\Migrations\Migration') && method_exists($class, 'down')) {
                $migrationClass = $class;
                break;
            }
        }

        if (!$migrationClass) {
            $this->error('マイグレーションクラスが見つかりませんでした。');
            return 1;
        }

        $migrationInstance = new $migrationClass();

        try {
            // down メソッドを直接実行
            $migrationInstance->down();
            $this->info("マイグレーション {$file} の down メソッドを実行しました。");
        } catch (\Exception $e) {
            $this->error("down の実行中にエラーが発生しました: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
