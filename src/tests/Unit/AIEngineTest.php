<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AIEngine;
use App\Models\Knowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIEngineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_knowledge_response_when_keyword_matches()
    {
        // テスト用に知識を作成
        Knowledge::create([
            'title' => 'テスト知識',
            'content' => 'これはテストの知識です。',
        ]);

        $aiEngine = new AIEngine();
        $response = $aiEngine->getResponse('テスト');

        $this->assertEquals('これはテストの知識です。', $response);
    }

    /** @test */
    public function it_returns_default_response_when_no_match_found()
    {
        $aiEngine = new AIEngine();
        $response = $aiEngine->getResponse('未登録のキーワード');

        $this->assertEquals('申し訳ありません、その知識はまだ教えられていません。', $response);
    }
}
