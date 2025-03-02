#!/bin/bash

# test_write.php を作成
cat << 'EOF' > test_write.php
<?php
$file = '/var/www/html/storage/framework/views/test_write.txt';
$result = file_put_contents($file, "Test write from PHP-FPM\n");
if ($result === false) {
    echo "Failed to write to {$file}\n";
} else {
    echo "Successfully wrote to {$file}\n";
    // テスト後にファイルを削除
    unlink($file);
}
?>
EOF

# PHP スクリプトを実行
php test_write.php

# 作成したスクリプトを削除
rm test_write.php
