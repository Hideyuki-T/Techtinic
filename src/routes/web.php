<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\chat\ChatDataController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//https://localhost:8080/chat-data  200 OK
//https://localhost:8080/chat-data-view  200 OK



Route::get('/', function () {
    return view('welcome');
});

//-------------------------------------

//全ての始まりページ
Route::get('/main', function () {
    return view('main.index');
});

//-------------------------------------

//TechtinicChat用のページ
Route::get('/chat', function () {
    return view('chat.index');
});

Route::get('/chat-data', [ChatDataController::class, 'index']);
Route::get('/chat-data-view', function(){
    return view('chat.data');
});
//-------------------------------------

//Game用のページ
Route::get('/game', function () {
    return view('game.index');
});

//-------------------------------------

//ECサイト用のページ
Route::get('/ec', function () {
    return view('ec.index');
});


//お気に入り用
Route::get('/url', function () {
    return view('url.index');
});

