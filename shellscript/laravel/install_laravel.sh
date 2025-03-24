#!/bin/bash

# Laravelのインストール
LARAVEL_VERSION="10.*"

APP_CONTAINER="main-app"
INSTALL_DIR="/var/www/html"

echo "-------------------------"
echo "Laravel ($LARAVEL_VERSION) を $APP_CONTAINER コンテナ内にインストールします。"
echo "-------------------------"

# コンテナの起動確認
if ! docker ps --format '{{.Names}}' | grep -q "^${APP_CONTAINER}$"; then
  echo "$APP_CONTAINER は起動していません。コンテナの起動をお願いします。"
  exit 1
fi

# Laravelの既存確認をローカルで
EXISTS=$(docker exec -it "$APP_CONTAINER" bash -c "[ -f '$INSTALL_DIR/artisan' ] && echo 'yes' || echo 'no'")
if [ "$EXISTS" == "yes" ]; then
  echo "Laravelが既にインストールされています。上書きしますか？(Y/n)"
  read -r REPLY
  if [ "$REPLY" != "Y" ]; then
    echo "インストールをキャンセルしました。"
    exit 1
  fi

  echo "既存のLaravelを削除中・・・"
  docker exec -it "$APP_CONTAINER" bash -c "rm -rf $INSTALL_DIR/{*,.*} 2>/dev/null"
  echo "削除後の確認:"
  docker exec -it "$APP_CONTAINER" bash -c "ls -la $INSTALL_DIR"
  REMAINING_FILES=$(docker exec -it "$APP_CONTAINER" bash -c "ls -A $INSTALL_DIR | wc -l")
  if [ "$REMAINING_FILES" -ne 0 ]; then
    echo "ディレクトリが完全に空ではありません。削除に失敗しました。"
    exit 1
  fi
fi

# Laravelのインストール
docker exec -it "$APP_CONTAINER" bash -c "
  echo 'composer のバージョンを確認中・・・';
  composer --version || (echo 'composer がインストールされていません。 ' && exit 1);

  echo 'Laravel ($LARAVEL_VERSION) をインストール中・・・';
  cd $INSTALL_DIR;
  composer create-project laravel/laravel --prefer-dist . '$LARAVEL_VERSION'

  echo '権限を設定中・・・';
  if [ -d "storage" ] && [ -d "bootstrap/cache" ]; then
    chmod -R 777 storage bootstrap/cache
  fi
  if [ -f "artisan" ]; then
    php artisan key:generate
  else
    echo "Laravelのインストールに失敗しました。"
    exit 1
  fi

  echo 'Laravel ($LARAVEL_VERSION) のインストールが完了しました。';
"

echo "-------------------------"
echo "Laravel ($LARAVEL_VERSION) を $APP_CONTAINER コンテナ内にセットアップ完了"
echo " 確認: docker exec -it $APP_CONTAINER bash -c 'cd /var/www/html && php artisan --version'"
echo "-------------------------"
