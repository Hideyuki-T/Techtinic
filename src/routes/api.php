<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ChatMessageController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('chat')->group(function () {
    Route::get('/messages', [ChatMessageController::class, 'index']);
    Route::post('/messages', [ChatMessageController::class, 'store']);
    Route::put('/messages/{id}', [ChatMessageController::class, 'update']);
    Route::delete('/messages/{id}', [ChatMessageController::class, 'destroy']);
});
