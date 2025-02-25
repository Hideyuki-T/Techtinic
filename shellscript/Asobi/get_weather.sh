#!/bin/bash

# 横浜市の天気を取得する
get_weather() {
    local location="Yokohama"
    echo "🌤 横浜市の天気情報 🌤"
    echo "----------------------------------"

    # シンプルな天気情報
    echo -n "📌 場所: "
    curl -s "wttr.in/${location}?format=%l"
    echo ""

    # 現在の天気
    local weather=$(curl -s "wttr.in/${location}?format=%C")
    local temp=$(curl -s "wttr.in/${location}?format=%t" | tr -d '+°C')
    local wind=$(curl -s "wttr.in/${location}?format=%w")
    local rain=$(curl -s "wttr.in/${location}?format=%p")

    # 天気に応じた絵文字を付ける
    case "$weather" in
        *晴れ*) icon="☀️" ;;
        *曇り*) icon="☁️" ;;
        *雨*) icon="🌧️" ;;
        *雷*) icon="⛈️" ;;
        *雪*) icon="❄️" ;;
        *霧*) icon="🌫️" ;;
        *) icon="🌍" ;;
    esac

    echo "🌦 天気: ${icon} ${weather}"
    echo "🌡 気温: ${temp}°C"
    echo "💨 風速: ${wind}"
    echo "☔ 降水量: ${rain}"

    echo "----------------------------------"
    echo "🌍 詳細情報:"
    curl -s "wttr.in/${location}?lang=ja"
}

# 服装を提案する
suggest_outfit() {
    local temp=$(curl -s "wttr.in/Yokohama?format=%t" | tr -d '+°C')

    if [[ $temp -ge 30 ]]; then
        outfit="🩳 半袖 & ショートパンツ"
    elif [[ $temp -ge 20 ]]; then
        outfit="👕 半袖"
    elif [[ $temp -ge 10 ]]; then
        outfit="🧥 軽いジャケット"
    elif [[ $temp -ge 0 ]]; then
        outfit="🧥 コート & 手袋"
    else
        outfit="🧣 厚手のコート & マフラー"
    fi

    echo "👕 今日のおすすめの服装: ${outfit}"
}

# 傘が必要かチェックする
check_umbrella() {
    local weather=$(curl -s "wttr.in/Yokohama?format=%C")

    if [[ "$weather" == *"雨"* ]] || [[ "$weather" == *"雷"* ]] || [[ "$weather" == *"雪"* ]]; then
        echo "☔ 雨が降りそうです！傘を持って行きましょう！"
    else
        echo "🌞 傘はいりません！"
    fi
}


# 実行
get_weather
echo "==============================="
suggest_outfit
check_umbrella
echo "==============================="
echo "🌟 良い天気だよ！今日も頑張ろう！🌟"
echo "==============================="
