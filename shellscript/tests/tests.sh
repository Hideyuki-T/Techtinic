#!/bin/bash

# Docker コンテナ名（テストを実行するコンテナ）
CONTAINER="techtinic-app"

# スピナー関数: コマンド実行中にシンプルな進捗表示を行う
run_command_with_spinner() {
    local cmd="$1"
    local output_file=$(mktemp)
    eval "$cmd" > "$output_file" 2>&1 &
    local pid=$!
    local spin='-\|/'
    local i=0
    while kill -0 $pid 2>/dev/null; do
        i=$(( (i+1) % 4 ))
        printf "\r%s" "${spin:$i:1}"
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
    echo "3) [E2E] Service Worker Integration Test (Puppeteer)"
    echo "   - ブラウザ環境でサービスワーカーの動作やネットワークリクエストの挙動を検証"
    echo "4) [Feature] IndexedDB Synchronization Test (Dusk)"
    echo "   - IndexedDB同期完了のインジケータが表示されるか検証"
    echo "5) [Feature] Responsive Layout Test for Desktop (Dusk)"
    echo "   - PCでのレイアウトが正しく表示されるか検証"
    echo "6) [Feature] Responsive Layout Test for Mobile (Dusk)"
    echo "   - モバイルでのレイアウトが正しく表示されるか検証"
    echo "7) [Feature] WebRoutesTest"
    echo "   - 各Webルートが正しく表示されるか検証"
    echo "8) [Feature] ChatControllerTest"
    echo "   - /chatエンドポイントのPOSTリクエストで正しいJSONが返るか検証"
    echo "9) [Unit] AIEngineTest"
    echo "   - AIEngineのキーワード検索ロジックを検証"
    echo "10) [Unit] ChatServiceTest"
    echo "    - チャットサービスの内部ロジックが期待通りか検証"
    echo "11) [Feature] SyncApiTest"
    echo "    - APIエンドポイントが正しいJSONを返すか検証"
    echo "12) [Feature] ExampleTest"
    echo "    - アプリケーションの基本動作を検証"
    echo "13) 全てのテストを実行"
    echo "q) 終了する"
    read -p "番号を入力: " choice

    case $choice in
        1)
            DESCRIPTION="[Feature] HomePage HTTPS Response Test: HTTPS通信でホームページが正しくレスポンスを返すか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=HomePageHttpsResponseTest"
            ;;
        2)
            DESCRIPTION="[Feature] Docker Container Communication Test: app、nginx、postgresql間の疎通ができるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=DockerContainerCommunicationTest"
            ;;
        3)
            DESCRIPTION="[E2E] Service Worker Integration Test (Puppeteer): ブラウザ環境でサービスワーカーの動作やネットワークリクエストの挙動を検証します。"
            # Puppeteer を用いて tests/E2E/ 内のテストを実行
            TEST_CMD="docker exec $CONTAINER npm run test:service-worker"
            ;;
        4)
            DESCRIPTION="[Feature] IndexedDB Synchronization Test: IndexedDB同期完了のインジケータが表示されるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan dusk --filter=IndexedDbSynchronizationTest"
            ;;
        5)
            DESCRIPTION="[Feature] Responsive Layout Test for Desktop: PC向けレイアウトが正しく表示されるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan dusk --filter=ResponsiveLayoutDesktopTest"
            ;;
        6)
            DESCRIPTION="[Feature] Responsive Layout Test for Mobile: モバイル向けレイアウトが正しく表示されるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan dusk --filter=ResponsiveLayoutMobileTest"
            ;;
        7)
            DESCRIPTION="[Feature] WebRoutesTest: 各Webルートが正しく表示されるか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=WebRoutesTest"
            ;;
        8)
            DESCRIPTION="[Feature] ChatControllerTest: /chatエンドポイントへのPOSTで正しいJSONが返るか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ChatControllerTest"
            ;;
        9)
            DESCRIPTION="[Unit] AIEngineTest: AIEngineの内部ロジックを検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=AIEngineTest"
            ;;
        10)
            DESCRIPTION="[Unit] ChatServiceTest: チャットサービスの内部ロジックが期待通りに動作するか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ChatServiceTest"
            ;;
        11)
            DESCRIPTION="[Feature] SyncApiTest: APIエンドポイントが正しいJSONを返すか検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=SyncApiTest"
            ;;
        12)
            DESCRIPTION="[Feature] ExampleTest: アプリケーションの基本動作を検証します。"
            TEST_CMD="docker exec $CONTAINER php artisan test --filter=ExampleTest"
            ;;
        13)
            DESCRIPTION="全てのテスト: プロジェクト全体のテストを実行します。"
            TEST_CMD="docker exec $CONTAINER php artisan test && docker exec $CONTAINER php artisan dusk"
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

    echo ""
    echo "$DESCRIPTION"
    echo ""
    read -p "このテストを本当に実行しますか？（Y/n）: " confirm

    if [[ "$confirm" == "Y" || "$confirm" == "y" || "$confirm" == "" ]]; then
        echo ""
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
