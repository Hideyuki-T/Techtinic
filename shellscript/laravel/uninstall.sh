#!/bin/bash

APP_CONTAINER="main-app"
INSTALL_DIR="/var/www/html"

echo "-------------------------"
echo "$APP_CONTAINER コンテナ内の Laravel をアンインストールします。"
echo "-------------------------"

if ! docker ps --format '{{.Names}}' | grep -q "^${APP_CONTAINER}$"; then
  echo "$APP_CONTAINER は起動していません。コンテナの起動をお願いします。現在起動中のコンテナは: $(docker ps --format　'{{.Names}}' | tr '\n' ', ')"
  exit 1
fi

docker exec -it "$APP_CONTAINER" bash -c "
  echo 'Laravel プロジェクトを削除中・・・';
  rm -rf $INSTALL_DIR/*
  echo 'Laravel のアンインストールが完了しました。';
"

echo "-------------------------"
echo "$APP_CONTAINER コンテナ内の Laravel をアンインストール完了しました。"
echo "-------------------------"
