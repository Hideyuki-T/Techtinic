<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Models\Tag;
Use App\Http\Models\Knowledge;
use App\Http\Models\User;
use App\Http\Models\Category;

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

// チャット関連ルートグループ（例: https://localhost:8080/chat）
Route::prefix('chat')->group(function () {
    Route::get('/knowledge', [ChatController::class, 'knowledge']);
    // チャット画面の表示（resources/views/techtinic/chat.blade.php）
    Route::get('/', fn() => view('techtinic.chat'));
    // チャットの API エンドポイント
    Route::post('/', [ChatController::class, 'chat']);
});

// 知識登録画面および処理（例: http://localhost:8080/teach）
Route::get('/teach', [KnowledgeController::class, 'create']);
Route::post('/teach', [KnowledgeController::class, 'store']);

// 知識一覧ページ（例: http://localhost:8080/knowledge）
Route::get('/knowledge', fn() => view('knowledge.knowledge'));



//以下データベース内確認用URL
Route::get('/tagData', function () {
    // YourModel は対象のモデル名に置き換えてください
    $data = \App\Models\Tag::all();
    return response()->json($data);
});
Route::get('/knowledgeData', function () {
    $data = \App\Models\Knowledge::all();
    return response()->json($data);
});
Route::get('/userData', function () {
    $data = \App\Models\User::all();
    return response()->json($data);
});
Route::get('/categoryData', function () {
    $data = \App\Models\Category::all();
    return response()->json($data);
});

