<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Models\Knowledge;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| ここでは、API ルートを定義します。これらのルートは自動的に "/api"
| プレフィックスが付与されますので、たとえば、"/sync" と定義すると、
| 外部からは "http://{ホスト}:ポート/api/sync" でアクセス可能です。
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// チャットの API エンドポイント
Route::post('/chat', [ChatController::class, 'chat']);

// データベースから知識データを取得するルート
Route::get('/sync', function () {
    // Knowledge のカテゴリーやタグも同時にロードする場合：
    $knowledgeData = Knowledge::with(['categories', 'tags'])->get();
    return response()->json(['knowledge' => $knowledgeData]);
});

// API で環境変数を取得するエンドポイント
Route::get('/config', function () {
    return response()->json([
        'sync_server_ip' => config('app.sync_server_ip'),
    ]);
});
