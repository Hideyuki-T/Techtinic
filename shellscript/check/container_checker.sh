#!/bin/bash

# jq の存在チェックと自動インストール（Ubuntu向け）
if ! command -v jq &> /dev/null; then
    echo "⚠ jq がインストールされていません。インストールを試みます..."
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y jq
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        brew install jq
    elif [[ "$OSTYPE" == "cygwin" ]] || [[ "$OSTYPE" == "msys" ]]; then
        echo "Windows 環境では jq を手動でインストールしてください: https://stedolan.github.io/jq/download/"
        exit 1
    else
        echo "⚠ このOSでは自動インストールがサポートされていません。手動でインストールしてください。"
        exit 1
    fi

    if ! command -v jq &> /dev/null; then
        echo "❌ jq のインストールに失敗しました。手動でインストールしてください。"
        exit 1
    fi
    echo "✅ jq のインストールが完了しました。"
fi

echo "===== Detailed Docker Container Information ====="

# 全コンテナ（停止中も含む）のIDを取得
containers=$(docker ps -a -q)
if [ -z "$containers" ]; then
    echo "コンテナが見つかりません。"
    exit 0
fi

# 各コンテナの詳細情報を順次出力
for container in $containers; do
    echo "------------------------------------------"

    # コンテナ名を取得（先頭のスラッシュを削除）
    container_name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
    echo "Container Name: $container_name"

    echo ""
    echo "【1. Port Mappings】"
    docker inspect --format='{{json .NetworkSettings.Ports}}' "$container" | jq .

    echo ""
    echo "【2. Network Settings】"
    docker inspect --format='{{json .NetworkSettings.Networks}}' "$container" | jq .

    echo ""
    echo "【3. Volumes / Mounts】"
    docker inspect --format='{{json .Mounts}}' "$container" | jq .

    echo ""
    echo "【4. Environment Variables】"
    docker inspect --format='{{json .Config.Env}}' "$container" | jq .

    echo ""
    echo "【5. IP Address Bindings】"
    docker inspect --format='{{range $net, $conf := .NetworkSettings.Networks}}Network {{$net}}: {{$conf.IPAddress}}{{"\n"}}{{end}}' "$container"

    echo ""
    echo "【6. Log / Cache Paths (Log Config)】"
    docker inspect --format='{{json .HostConfig.LogConfig}}' "$container" | jq .

    echo ""
done

echo "===== End of Detailed Report ====="

###########################################
# 以下、各カテゴリー別に出力するかどうか選択
###########################################

# コンテナID一覧
read -p "全コンテナのID一覧を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- 全コンテナのID一覧 -----"
    docker ps -a -q
    echo ""
fi

# コンテナ名一覧
read -p "コンテナ名一覧を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- コンテナ名一覧 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "$name"
    done
    echo ""
fi

# ポートマッピング
read -p "ポートマッピング情報を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- ポートマッピング情報 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "Container: $name"
        docker inspect --format='{{json .NetworkSettings.Ports}}' "$container" | jq .
        echo ""
    done
fi

# ネットワーク設定
read -p "ネットワーク設定情報を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- ネットワーク設定情報 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "Container: $name"
        docker inspect --format='{{json .NetworkSettings.Networks}}' "$container" | jq .
        echo ""
    done
fi

# ボリューム・マウントパス
read -p "ボリューム・マウントパス情報を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- ボリューム・マウントパス情報 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "Container: $name"
        docker inspect --format='{{json .Mounts}}' "$container" | jq .
        echo ""
    done
fi

# 環境変数
read -p "環境変数情報を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- 環境変数情報 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "Container: $name"
        docker inspect --format='{{json .Config.Env}}' "$container" | jq .
        echo ""
    done
fi

# IPアドレスのバインディング
read -p "IPアドレスのバインディング情報を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- IPアドレスのバインディング情報 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "Container: $name"
        docker inspect --format='{{range $net, $conf := .NetworkSettings.Networks}}Network {{$net}}: {{$conf.IPAddress}}{{"\n"}}{{end}}' "$container"
        echo ""
    done
fi

# ログ／キャッシュのパス（Log Config）
read -p "ログ／キャッシュのパス（Log Config）情報を出力しますか？ (Y/n): " ans
if [[ "$ans" == "Y" || "$ans" == "y" || -z "$ans" ]]; then
    echo "----- ログ／キャッシュのパス（Log Config）情報 -----"
    for container in $containers; do
        name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
        echo "Container: $name"
        docker inspect --format='{{json .HostConfig.LogConfig}}' "$container" | jq .
        echo ""
    done
fi


echo "===== End of Category Report ====="


# ===== End of Category Report =====

###########################################
# 以下、新規コンテナ作成時に一致すると困る設定の一覧出力
###########################################

echo "===== Conflict Settings Summary ====="
echo "各コンテナで使用中の bindマウントおよびボリュームの設定一覧"
echo ""

for container in $containers; do
    container_name=$(docker inspect --format='{{.Name}}' "$container" | sed 's/^\/\(.*\)/\1/')
    echo "Container: $container_name"

    # Mounts情報を取得し、Windowsのパスのバックスラッシュをエスケープ
    mounts=$(docker inspect --format='{{json .Mounts}}' "$container" | sed 's/\\/\\\\/g')

    echo "$mounts" | jq -c '.[]' | while read mount; do
        type=$(echo $mount | jq -r '.Type')
        if [ "$type" == "bind" ]; then
            source=$(echo $mount | jq -r '.Source')
            dest=$(echo $mount | jq -r '.Destination')
            echo "  [Bind Mount] Host Directory (Source): $source"
            echo "             Container Mount (Destination): $dest"
        elif [ "$type" == "volume" ]; then
            volname=$(echo $mount | jq -r '.Name')
            source=$(echo $mount | jq -r '.Source')
            dest=$(echo $mount | jq -r '.Destination')
            echo "  [Volume] Volume Name: $volname"
            echo "           Docker-managed Host Path (Source): $source"
            echo "           Container Mount (Destination): $dest"
        fi
    done
    echo ""
done

echo "===== End of Conflict Settings Summary ====="
