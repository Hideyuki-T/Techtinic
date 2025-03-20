<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ChatMessageController;
use App\Http\Controllers\Chat\ChatDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| ここではアプリケーションの API ルートを登録。
| これらのルートは RouteServiceProvider によって "api" ミドルウェアグループに
| 割り当てられる
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Chat関連のAPIルート
Route::prefix('chat')->group(function () {
    Route::get('/messages', [ChatMessageController::class, 'index']);
    Route::post('/messages', [ChatMessageController::class, 'store']);
    Route::put('/messages/{id}', [ChatMessageController::class, 'update']);
    Route::delete('/messages/{id}', [ChatMessageController::class, 'destroy']);

    // Chatデータ取得API
    Route::get('/data', [ChatDataController::class, 'index']);
});
