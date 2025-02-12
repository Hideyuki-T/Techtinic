<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\KnowledgeController;


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

// ルートページ
Route::get('/', fn() => view('welcome'));

// チャット関連ルートグループ（例: http://localhost:8080/chat）
Route::prefix('chat')->group(function () {
    // チャット画面の表示（resources/views/techtinic/chat.blade.php）
    Route::get('/', fn() => view('techtinic.chat'));
    // チャットの API エンドポイント
    Route::post('/', [ChatController::class, 'chat']);
});

// 知識登録画面および処理（例: http://localhost:8080/teach）
// GET でフォーム表示、POST で登録処理を行う
Route::get('/teach', [KnowledgeController::class, 'create']);
Route::post('/teach', [KnowledgeController::class, 'store']);
