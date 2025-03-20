<?php

namespace App\Http\Controllers\Tetris;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Score;

class TetrisController extends Controller
{
    // ゲーム画面を表示する
    public function index()
    {
        return view('game.tetris.tetris');
    }

    // ゲーム終了後のスコアをDBに保存する
    public function storeScore(Request $request)
    {
        // バリデーション例
        $data = $request->validate([
            'player_name' => 'required|string|max:50',
            'score'       => 'required|integer|min:0',
        ]);

        Score::create($data);

        return response()->json(['message' => 'スコアが保存されました！']);
    }
}
