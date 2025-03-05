#!/bin/bash
# check_communication.sh
# 各コンテナ間の通信確認スクリプト

# --- techtinic-nginx -> techtinic-app の接続テスト ---
echo "【テスト1】techtinic-nginx から techtinic-app のポート9000への接続確認"
docker exec techtinic-nginx sh -c "nc -zv techtinic-app 9000"
if [ $? -eq 0 ]; then
    echo "成功: techtinic-nginx は techtinic-app のポート9000に接続できます。"
else
    echo "失敗: techtinic-nginx は techtinic-app のポート9000に接続できません。"
fi
echo "-------------------------------------"

# --- techtinic-app -> techtinic-db の接続テスト ---
echo "【テスト2】techtinic-app から techtinic-db のポート5432への接続確認"
docker exec techtinic-app sh -c "nc -zv techtinic-db 5432"
if [ $? -eq 0 ]; then
    echo "成功: techtinic-app は techtinic-db のポート5432に接続できます。"
else
    echo "失敗: techtinic-app は techtinic-db のポート5432に接続できません。"
fi
echo "-------------------------------------"

echo "通信テストを完了しました。"
