<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CustomRollbackFolderCommand extends Command
{
    // コマンドのシグネチャ。引数 folder で対象フォルダ名を受け取る（例: custom_migrations）
    protected $signature = 'db:custom-rollback-folder {folder : マイグレーションが格納されているフォルダ名 (例: custom_migrations)}';

    // コマンドの説明
    protected $description = '指定したフォルダ内のマイグレーションファイルのみを rollback する';

    public function handle()
    {
        $folder = $this->argument('folder');
        $migrationFolder = database_path('migrations' . DIRECTORY_SEPARATOR . $folder);

        if (!is_dir($migrationFolder)) {
            $this->error("指定されたフォルダが存在しません: {$folder}");
            return 1;
        }

        // 対象フォルダ内のマイグレーションファイルを取得（拡張子 .php のファイル）
        $files = glob($migrationFolder . DIRECTORY_SEPARATOR . '*.php');

        if (empty($files)) {
            $this->info("フォルダ内にマイグレーションファイルが見つかりません: {$folder}");
            return 0;
        }

        // マイグレーションファイルはタイムスタンプ付きファイル名であることを前提に、降順にソート（最新のものから rollback する）
        usort($files, function($a, $b) {
            return strcmp(basename($b), basename($a));
        });

        foreach ($files as $file) {
            $this->info("Rolling back migration file: " . basename($file));

            // ファイル読み込み前のクラス一覧を取得
            $declaredBefore = get_declared_classes();

            // マイグレーションファイルを読み込む
            require_once $file;

            // 読み込み後のクラス一覧から新たに追加されたクラスを抽出
            $declaredAfter = get_declared_classes();
            $newClasses = array_diff($declaredAfter, $declaredBefore);

            $migrationClass = null;
            foreach ($newClasses as $class) {
                if (is_subclass_of($class, 'Illuminate\Database\Migrations\Migration') && method_exists($class, 'down')) {
                    $migrationClass = $class;
                    break;
                }
            }

            if (!$migrationClass) {
                $this->error("Migration class not found in file: " . basename($file));
                continue;
            }

            try {
                $migrationInstance = new $migrationClass();
                $migrationInstance->down();
                $this->info("Rolled back: " . basename($file));
            } catch (\Exception $e) {
                $this->error("Error rolling back " . basename($file) . ": " . $e->getMessage());
            }
        }

        return 0;
    }
}
