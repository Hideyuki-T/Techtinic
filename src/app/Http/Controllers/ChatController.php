<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatService;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function chat(Request $request)
    {
        $input = trim($request->input('message'));
        $result = $this->chatService->processMessage($input);

        // ここでは、Web版では基本的に1回のリクエストで結果を返す仕様とする
        // 選択肢モードの場合も、選択肢情報（options）を含めて返す
        return response()->json($result);
    }
}
