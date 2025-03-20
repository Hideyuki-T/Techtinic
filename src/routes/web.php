<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ChatDataController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Tetris\TetrisController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| ここではWebアプリケーションのページ表示用ルートを登録
|　
|
*/

Route::get('/', function () {
    return view('welcome');
});

//-------------------------------------
// メインページ
Route::get('/main', function () {
    return view('main.index');
});

//-------------------------------------
// TechtinicChatのWebページ
Route::get('/chat', function () {
    return view('chat.index');
});

Route::get('/chat/indexedDBUtil.js', function () {
    $path = resource_path('views/chat/indexedDBUtil.js');
    if (!File::exists($path)) {
        abort(404);
    }
    $content = File::get($path);
    return Response::make($content, 200, [
        'Content-Type' => 'application/javascript'
    ]);
});

// `chat-data-view` はWebページとして利用
Route::get('/chat-data-view', function () {
    return view('chat.data');
});

//-------------------------------------
// Gameページ
Route::get('/game', function () {
    return view('game.index');
});

//テトリス用ページ
Route::get('/tetris', [TetrisController::class, 'index']);
Route::post('/tetris/score', [TetrisController::class, 'storeScore']);

//-------------------------------------
// ECサイトページ
Route::get('/ec', function () {
    return view('ec.index');
});

//-------------------------------------
// お気に入り用ページ
Route::get('/url', function () {
    return view('url.index');
});
