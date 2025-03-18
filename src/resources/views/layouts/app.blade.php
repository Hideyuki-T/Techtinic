<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Techtinic App')</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <!-- CSRF トークン -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- axiosライブラリ -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Service Worker 登録 -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js', { type: 'module' })
                .then(function(registration) {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch(function(error) {
                    console.error('Service Worker registration failed:', error);
                });
        }
    </script>
    <!-- idbライブラリを ESモジュール形式で読み込み、グローバルに公開 -->
    <script type="module">
        import { openDB } from "/js/idb.min.js";
        window.idb = { openDB };
    </script>
    @yield('head')
</head>
<body class="@yield('body-class')">
<div id="app">
    @yield('content')
    <button id="installButton" style="display: none;">アプリをインストール</button>
</div>
<!-- main.js の読み込み -->
<script src="/js/main.js"></script>
@yield('scripts')
</body>
</html>
