function syncDataFromPC() {
    fetch("http://192.168.243.131:8080/api/sync")
        .then(response => response.json())
        .then(data => {
            console.log("同期データ取得:", data);
            openDatabase(() => saveDataToIndexedDB(data));
        })
        .catch(error => console.error("同期失敗:", error));
}

// ページ読み込み時に同期を実行
document.addEventListener("DOMContentLoaded", syncDataFromPC);
