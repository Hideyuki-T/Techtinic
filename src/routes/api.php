<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Models\Knowledge;
use App\Services\SystemStatusService;
use App\Http\Controllers\KnowledgeController;
use App\Models\Tag;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| ここでは、API ルートを定義します。これらのルートは自動的に "/api"
| プレフィックスが付与されます。
| なので、たとえば、"/sync" と定義すると、
| 外部からは "http://{ホスト}:ポート/api/sync" でアクセス可能。
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

// 知識データ削除用のエンドポイントを追加
Route::delete('/knowledge/{id}', [KnowledgeController::class, 'destroy']);
// 知識データ更新用のエンドポイントを追加
Route::put('/knowledge/{id}', [KnowledgeController::class, 'update']);

// タグの一覧を取得するエンドポイント
Route::get('/tags', function () {
    return response()->json(Tag::all());
});
//タグ削除用のエンドポイント
Route::delete('/tags/{id}', [KnowledgeController::class, 'destroyTag']);

// API で環境変数を取得するエンドポイント
Route::get('/config', function () {
    return response()->json([
        'sync_server_ip' => env('SYNC_SERVER_IP', 'localhost'),
    ]);
});

//個のエンドポイントは SystemStatusService::isOnline() の結果を JSON で返す。
Route::get('/system/status', function () {
    return response()->json([
        'online' => SystemStatusService::isOnline(),
    ]);
});
