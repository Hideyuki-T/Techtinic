#!/bin/bash

# --- nginx 設定ファイルの更新 ---
NGINX_CONF="../../docker/nginx/conf.d/default.conf"
if [ ! -f "$NGINX_CONF" ]; then
    echo "Error: $NGINX_CONF が見つからないや。。。"
    exit 1
fi

echo "------------------------------------------------------------"
echo "nginx 設定ファイルの更新を開始するよ..."

# ssl_certificate のパスを更新（/etc/nginx/ssl/以下のファイル名部分を置換）
sed -i 's|\(ssl_certificate\s\+/etc/nginx/ssl/\)[^;]*;|\1server-cert.pem;|' "$NGINX_CONF"
if [ $? -ne 0 ]; then
    echo "Error: ssl_certificate の更新に失敗してしまいました。。。ごめん。"
    exit 1
fi

# ssl_certificate_key のパスを更新（/etc/nginx/ssl/以下のファイル名部分を置換）
sed -i 's|\(ssl_certificate_key\s\+/etc/nginx/ssl/\)[^;]*;|\1server-key.pem;|' "$NGINX_CONF"
if [ $? -ne 0 ]; then
    echo "Error: ssl_certificate_key の更新に失敗してしまいました。。。ごめん。"
    exit 1
fi

echo "nginx 設定ファイルの更新が完了したよ。更新後の内容は以下："
cat "$NGINX_CONF"
echo "------------------------------------------------------------"
