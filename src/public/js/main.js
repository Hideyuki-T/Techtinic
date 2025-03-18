function updateOnlineStatus() {
    const banner = document.getElementById('offline-banner');
    if (banner) {
        if (navigator.onLine) {
            banner.style.display = 'none';
        } else {
            banner.style.display = 'block';
        }
    } else {
        console.warn('offline-banner 要素が見つかりません。');
    }
}

window.addEventListener('load', updateOnlineStatus);
window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

// PWA インストール促しの処理
let deferredPrompt;
const installButton = document.getElementById('installButton');

window.addEventListener('beforeinstallprompt', (e) => {
    // デフォルトのインストールプロンプトを抑制
    e.preventDefault();
    deferredPrompt = e;
    // インストールボタンを表示する
    if (installButton) {
        installButton.style.display = 'block';
    }
});

if (installButton) {
    installButton.addEventListener('click', () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('ユーザーがインストールを承認しました');
                } else {
                    console.log('ユーザーがインストールを拒否しました');
                }
                deferredPrompt = null;
                installButton.style.display = 'none';
            });
        }
    });
}
