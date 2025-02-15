function updateOnlineStatus() {
    const banner = document.getElementById('offline-banner');
    if (navigator.onLine) {
        banner.style.display = 'none';
    } else {
        banner.style.display = 'block';
    }
}

window.addEventListener('load', updateOnlineStatus);
window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
