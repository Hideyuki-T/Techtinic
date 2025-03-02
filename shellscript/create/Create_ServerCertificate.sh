#!/bin/bash
# create_サーバー証明書.sh
# このスクリプトは、../../docker/nginx/ssl 内で CA/サーバー証明書を生成します。

# SSLディレクトリへの相対パス（実行ディレクトリから見たパス）
SSL_DIR="../../docker/nginx/ssl"
if [ ! -d "$SSL_DIR" ]; then
    echo "Error: $SSL_DIR が見つかりません。"
    exit 1
fi
cd "$SSL_DIR" || exit 1

echo "------------------------------------------------------------"
echo "【openssl.cnf の内容です。】"
cat openssl.cnf
echo "------------------------------------------------------------"
echo "【openssl-csr.cnf の内容です。】"
cat openssl-csr.cnf
echo "------------------------------------------------------------"

# ユーザーに最終確認
read -p "上記の設定内容で証明書を生成します！。。。か？（Y/n）： " answer
if [[ "$answer" != "Y" && "$answer" != "y" && "$answer" != "" ]]; then
    echo "生成を中止します。"
    exit 0
fi

echo "証明書の生成を開始するね！！。"

# --- CA 関連 ---
# ※ 既存の CA 関連ファイルがある場合はバックアップする処理を追加しても良い

# CA の秘密鍵生成
echo "CA の秘密鍵 (ca-key.pem) を生成中だよ..."
openssl genrsa -out ca-key.pem 2048
if [ $? -ne 0 ]; then
    echo "Error: CA の秘密鍵生成に失敗してしまいました。。。ごめん"
    exit 1
fi

# CA の自己署名証明書生成（openssl.cnf を使用、拡張設定 v3_ca を適用）
echo "CA の自己署名証明書 (ca-cert.pem) を生成中だよ..."
openssl req -x509 -new -nodes -key ca-key.pem -sha256 -days 1024 -out ca-cert.pem -config openssl.cnf -extensions v3_ca
if [ $? -ne 0 ]; then
    echo "Error: CA の自己署名証明書生成に失敗しちゃった。。。ごめん"
    exit 1
fi

# --- サーバー証明書関連 ---
# サーバー秘密鍵生成
echo "サーバー秘密鍵 (server-key.pem) を生成中だよ..."
openssl genrsa -out server-key.pem 2048
if [ $? -ne 0 ]; then
    echo "Error: サーバー秘密鍵生成に失敗してしまいました。。。ごめん"
    exit 1
fi

# CSR 生成（openssl-csr.cnf を使用して authorityKeyIdentifier を除外）
echo "サーバー証明書署名要求 (server.csr) を生成中だよ..."
openssl req -new -key server-key.pem -out server.csr -config openssl-csr.cnf
if [ $? -ne 0 ]; then
    echo "Error: CSR の生成に失敗してしまいました。。。ごめん"
    exit 1
fi

# サーバー証明書発行（CA署名、最終的な拡張設定 v3_server を使用）
echo "サーバー証明書 (server-cert.pem) を発行中だよ..."
openssl x509 -req -in server.csr -CA ca-cert.pem -CAkey ca-key.pem -CAcreateserial -out server-cert.pem -days 500 -sha256 -extfile openssl.cnf -extensions v3_server
if [ $? -ne 0 ]; then
    echo "Error: サーバー証明書の発行に失敗してしまいました。。。ごめん"
    exit 1
fi

# --- 生成結果の確認 ---
echo "------------------------------------------------------------"
echo "生成されたファイル一覧（find コマンドで確認）："
find . -maxdepth 1 -type f \( -name "ca-key.pem" -o -name "ca-cert.pem" -o -name "server-key.pem" -o -name "server.csr" -o -name "server-cert.pem" -o -name "ca-cert.srl" \)
echo "------------------------------------------------------------"

echo "各種ファイルの説明："
echo "・ca-key.pem         : CA の秘密鍵"
echo "・ca-cert.pem        : CA の自己署名証明書（クライアントにインストール）"
echo "・server-key.pem     : サーバーの秘密鍵"
echo "・server.csr         : サーバー証明書署名要求（CSR）"
echo "・server-cert.pem    : CA 署名済みのサーバー証明書"
echo "・ca-cert.srl        : シリアル番号ファイル（自動生成）"
echo "------------------------------------------------------------"

echo "nginx の設定ファイル (docker/nginx/conf.d/default.conf) を、生成された証明書に合わせて更新するの忘れないでね。"
echo "※ ssl_certificate に server-cert.pem、ssl_certificate_key に server-key.pem を指定するようにね。"
echo "------------------------------------------------------------"

echo "完了しましたよ！"
echo "では、以下の手順を実施してね："
echo "1. ca-cert.pem を PC と iPhone にインストールし、信頼済みルート証明書として登録する。"
echo "2. docker compose で nginx コンテナを再起動する（例: docker compose restart web）。"
echo "------------------------------------------------------------"

exit 0
