<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;


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
//http://localhost::8080/chat

//ルートページ
Route::get('/', fn() => view('welcome'));

//チャット関連ルートグループ
Route::prefix('chat')->group(function (){
    //チャット画面の表示
    Route::get('/', fn() => view('techtinic.chat'));
    //チャットのAPIエンドポイント
    Route::post('/', [ChatController::class, 'chat']);
});
//知識登録画面
Route::view('/teach', 'knowledge.teach');
