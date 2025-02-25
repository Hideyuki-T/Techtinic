<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ChatService;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Knowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = new ChatService();
    }

    /**
     * テスト: 「どんなことを知ってる？」の入力で、タグもカテゴリーもない場合
     * → デフォルト応答が返ることを確認する
     */
    public function test_processMessage_returns_default_response_when_no_tags_and_categories()
    {
        // DBは空の状態
        $result = $this->chatService->processMessage("どんなことを知ってる？");

        $this->assertEquals('default', $result['mode']);
        $this->assertStringContainsString("何も知らない", $result['response']);
    }

    /**
     * テスト: 「どんなことを知ってる？」の入力で、タグがないがカテゴリーが存在する場合
     * → カテゴリー選択モードで、カテゴリー一覧が返されることを確認する
     */
    public function test_processMessage_returns_category_selection_when_no_tags_but_categories_exist()
    {
        // カテゴリーだけ作成
        Category::create(['name' => 'カテゴリA']);

        $result = $this->chatService->processMessage("どんなことを知ってる？");

        $this->assertEquals('category_selection', $result['mode']);
        $this->assertArrayHasKey('options', $result);
        $this->assertContains('カテゴリA', $result['options']);
    }

    /**
     * テスト: 「どんなことを知ってる？」の入力で、タグが存在する場合
     * → タグ選択モードで、タグ一覧が返されることを確認する
     */
    public function test_processMessage_returns_tag_selected_when_tags_exist()
    {
        // タグを作成
        Tag::create(['name' => 'tag1']);

        $result = $this->chatService->processMessage("どんなことを知ってる？");

        $this->assertEquals('tag_selected', $result['mode']);
        $this->assertArrayHasKey('options', $result);
        $this->assertContains('tag1', $result['options']);
    }

    /**
     * テスト: タグ名を入力した場合、関連知識が存在するならタイトル選択モードで返す
     */
    public function test_processMessage_returns_title_selected_when_input_matches_tag_and_has_knowledge()
    {
        // タグ作成
        $tag = Tag::create(['name' => 'exampletag']);
        // 知識作成し、タグと関連付け
        $knowledge = Knowledge::create([
            'title' => '知識タイトル',
            'content' => '知識内容'
        ]);
        // 多対多の関連付けを仮定
        $tag->knowledges()->attach($knowledge->id);

        // 入力に対して
        $result = $this->chatService->processMessage("exampletag");

        $this->assertEquals('title_selected', $result['mode']);
        $this->assertArrayHasKey('options', $result);
        $this->assertContains('知識タイトル', $result['options']);
    }

    /**
     * テスト: 知識のタイトルを入力した場合、該当する知識内容が返される
     */
    public function test_processMessage_returns_knowledge_content_when_input_matches_knowledge_title()
    {
        // 知識作成
        $knowledge = Knowledge::create([
            'title' => 'テスト知識',
            'content' => 'これはテストの知識です。'
        ]);

        $result = $this->chatService->processMessage("テスト知識");

        $this->assertEquals('default', $result['mode']);
        $this->assertStringContainsString('これはテストの知識です。', $result['response']);
    }

    /**
     * テスト: 上記のいずれにも該当しない場合、デフォルト応答が返される
     */
    public function test_processMessage_returns_default_response_for_unmatched_input()
    {
        $result = $this->chatService->processMessage("不明な入力");

        $this->assertEquals('default', $result['mode']);
        $this->assertStringContainsString("申し訳ありません", $result['response']);
    }

    /**
     * テスト: processKnowledge() メソッドは「どんなことを知ってる？」の入力結果と同じを返す
     */
    public function test_processKnowledge_returns_same_as_processMessage_for_fixed_input()
    {
        $result1 = $this->chatService->processMessage("どんなことを知ってる？");
        $result2 = $this->chatService->processKnowledge();

        $this->assertEquals($result1, $result2);
    }
}
