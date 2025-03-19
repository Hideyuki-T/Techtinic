<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ChatDataController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| ここではWebアプリケーションのページ表示用ルートを登録
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

// `chat-data-view` はWebページとして利用
Route::get('/chat-data-view', function () {
    return view('chat.data');
});

//-------------------------------------
// Gameページ
Route::get('/game', function () {
    return view('game.index');
});

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
