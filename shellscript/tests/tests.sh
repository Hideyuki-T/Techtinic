#!/bin/bash

# Docker コンテナ名（テストを実行するコンテナ）
CONTAINER="techtinic-app"

# スピナー関数: コマンド実行中に回転アニメーションを表示する
run_command_with_spinner() {
    local cmd="$1"
    local output_file=$(mktemp)
    eval "$cmd" > "$output_file" 2>&1 &
    local pid=$!
    local spin='-\|/'
    local i=0
    while kill -0 $pid 2>/dev/null; do
        i=$(( (i+1) % 4 ))
        printf "\rテスト実行中... ${spin:$i:1}"
        sleep 0.1
    done
    wait $pid
    printf "\r"  # 行頭に戻す
    cat "$output_file"
    rm "$output_file"
}

while true; do
    echo "----------------------------------------"
    echo "実行するテストを選択してください:"
    echo "1) [Feature] HomePage HTTPS Response Test"
    echo "   - HTTPS通信でホームページが正しくレスポンスを返すか検証"
    echo "2) [Feature] Docker Container Communication Test"
    echo "   - app、nginx、postgresqlの各コンテナ間で疎通ができるか検証"
    echo "3) [Feature] Service Worker Registration Test"
    echo "   - ブラウザがサービスワーカーを正しく登録しているか検証"
    echo "4) [Feature] IndexedDB Synchronization Test"
    echo "   - IndexedDBに同期済みデータが正しく保存され、オフラインで利用できるか検証"
    echo "5) [Feature] Responsive Layout Test for Desktop"
    echo "   - PCでの表示が期待通りのレイアウトかを検証"
    echo "6) [Feature] Responsive Layout Test for Mobile"
    echo "   - スマホでの表示が最適化され、UIが正しく機能するかを検証"
    echo "7) [Feature] WebRoutesTest"
    echo "   - ホーム、チャット、知識登録・一覧などのWebルートが正しく表示されるか検証"
    echo "8) [Feature] ChatControllerTest"
    echo "   - /chatエンドポイントのPOSTリクエストに対して正しいJSONレスポンスが返るか検証"
    echo "9) [Unit] AIEngineTest"
    echo "   - AIEngineが入力キーワードに基づき知識応答またはデフォルト応答を返すか検証"
    echo "10) [Unit] ChatServiceTest"
    echo "    - チャットサービスの内部ロジック（例:'どんなことを知ってる？'の入力処理）が正しく動作するか検証"
    echo "11) [Feature] SyncApiTest"
    echo "    - /api/syncおよび/api/configエンドポイントが正しいJSON構造と内容を返すか検証"
    echo "12) [Feature] ExampleTest"
    echo "    - アプリケーションの基本動作（ルートへのアクセス等）が正常に行われるか検証"
    echo "13) 全てのテストを実行"
    echo "q) 終了する"
    read -p "番号を入力: " choice

    case $choice in
        1)
            DESCRIPTION="[Feature] HomePage HTTPS Response Test: HTTPS通信でホームページが正しくレスポンスを返すか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=HomePageHttpsResponseTest"
            ;;
        2)
            DESCRIPTION="[Feature] Docker Container Communication Test: app、nginx、postgresql間のネットワーク疎通ができるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=DockerContainerCommunicationTest"
            ;;
        3)
            DESCRIPTION="[Feature] Service Worker Registration Test: ブラウザがサービスワーカーを正しく登録しているか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ServiceWorkerRegistrationTest"
            ;;
        4)
            DESCRIPTION="[Feature] IndexedDB Synchronization Test: IndexedDBに同期済みデータが正しく保存され、オフラインで利用可能か検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=IndexedDbSynchronizationTest"
            ;;
        5)
            DESCRIPTION="[Feature] Responsive Layout Test for Desktop: PCでの表示が期待通りのレイアウトか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ResponsiveLayoutDesktopTest"
            ;;
        6)
            DESCRIPTION="[Feature] Responsive Layout Test for Mobile: スマホでの表示が最適化され、UIが正しく機能するか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ResponsiveLayoutMobileTest"
            ;;
        7)
            DESCRIPTION="[Feature] WebRoutesTest: ホーム、チャット、知識登録・一覧などのWebルートが正しく表示されるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=WebRoutesTest"
            ;;
        8)
            DESCRIPTION="[Feature] ChatControllerTest: /chatエンドポイントのPOSTリクエストに対して正しいJSONレスポンスが返るか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ChatControllerTest"
            ;;
        9)
            DESCRIPTION="[Unit] AIEngineTest: AIEngineが入力キーワードに基づき知識応答またはデフォルト応答を返すか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=AIEngineTest"
            ;;
        10)
            DESCRIPTION="[Unit] ChatServiceTest: チャットサービスの内部ロジック（例:'どんなことを知ってる？'の入力処理）が正しく動作するか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ChatServiceTest"
            ;;
        11)
            DESCRIPTION="[Feature] SyncApiTest: /api/syncおよび/api/configエンドポイントが正しいJSON構造と内容を返すか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=SyncApiTest"
            ;;
        12)
            DESCRIPTION="[Feature] ExampleTest: アプリケーションの基本動作（ルートへのアクセス等）が正常に行われるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ExampleTest"
            ;;
        13)
            DESCRIPTION="全てのテスト: プロジェクト全体のテストを実行します。"
            TEST_CMD="docker exec $CONTAINER php artisan test"
            ;;
        q|Q)
            echo "終了します。"
            exit 0
            ;;
        *)
            echo "無効な選択です。"
            continue
            ;;
    esac

    # 説明文を表示
    echo ""
    echo "$DESCRIPTION"
    echo ""
    read -p "このテストを本当に実行しますか？（Y/n）: " confirm

    if [[ "$confirm" == "Y" || "$confirm" == "y" || "$confirm" == "" ]]; then
        echo ""
        echo "テスト実行中..."
        run_command_with_spinner "$TEST_CMD"
    else
        echo "実行をキャンセルしました。メニューに戻ります。"
    fi

    echo ""
    read -p "別のテストを実行しますか？（Y/n）: " again
    if [[ "$again" == "n" || "$again" == "N" ]]; then
        echo "終了します。"
        exit 0
    fi
done
