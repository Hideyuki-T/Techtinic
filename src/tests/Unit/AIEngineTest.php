<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AIEngine;
use App\Models\Knowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIEngineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * キーワードにマッチする知識が存在する場合、その知識の内容を返すか検証するテスト
     *
     * @return void
     */
    public function test_getResponse_returns_knowledge_content_when_keyword_matches()
    {
        // テスト用の知識データを作成
        Knowledge::create([
            'title'   => 'テスト知識',
            'content' => 'これはテストの知識です。'
        ]);

        $aiEngine = new AIEngine();
        // タイトルに "テスト" が含まれているので、知識が見つかるはず
        $response = $aiEngine->getResponse('テスト');

        $this->assertEquals('これはテストの知識です。', $response);
    }

    /**
     * キーワードにマッチする知識が存在しない場合、デフォルトの応答を返すか検証するテスト
     *
     * @return void
     */
    public function test_getResponse_returns_default_response_when_no_match_found()
    {
        $aiEngine = new AIEngine();
        $response = $aiEngine->getResponse('未登録のキーワード');

        $this->assertEquals("それが何かはまだ知らないや。ごめん。。", $response);
    }
}
