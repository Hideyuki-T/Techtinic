<?php

namespace App\Services;

use App\Models\Knowledge;

class AIEngine
{
    /**
     * ユーザーの入力テキストに基づいて適切な応答を生成する
     *
     * @param string $input
     * @return string
     */
    public function getResponse(string $input): string
    {
        // シンプルなキーワードマッチングの例
        $knowledge = Knowledge::where('title', 'LIKE', "%{$input}%")
            ->orWhere('content', 'LIKE', "%{$input}%")
            ->first();

        if ($knowledge) {
            return $knowledge->content;

        }

            return "それが何かはまだ知らないや。ごめん。。";

    }
}
