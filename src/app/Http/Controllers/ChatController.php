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

    /**
     * チャット画面からのメッセージ処理
     */
    public function chat(Request $request)
    {
        $input = trim($request->input('message'));
        $result = $this->chatService->processMessage($input);

        // Web版では基本的に1回のリクエストで結果を返す仕様。
        // 選択肢モードの場合も、選択肢情報（options）を含めて返す。
        return response()->json($result);
    }

    /**
     * チャット画面から知識情報を取得するためのエンドポイント
     */
    public function knowledge()
    {
        // ChatService 側に知識情報を取得する処理が実装されていると仮定（例: processKnowledge()）
        $result = $this->chatService->processKnowledge();

        // 取得した結果を JSON 形式で返す
        return response()->json($result);
    }
}
