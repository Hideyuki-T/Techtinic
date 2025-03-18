<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ホームページ (GET /) が welcome ビューを返すか検証
     */
    public function test_home_page_displays_welcome_view()
    {
        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertSee('Techtinic Chat'); // 実際の表示テキストに合わせて調整
    }

    /**
     * /chat/knowledge ルートの検証
     */
    public function test_chat_knowledge_route()
    {
        $response = $this->get('/chat/knowledge');
        $response->assertStatus(200);
    }

    /**
     * /chat ルート（GET）の検証
     * チャット画面（例: techtinic.chat ビュー）が正しく表示されるか検証
     */
    public function test_chat_page_displays_chat_view()
    {
        $response = $this->get('/chat');
        $response->assertStatus(200)
            ->assertSee('Techtinic Chat'); // ヘッダー等の主要テキスト
    }

    /**
     * /chat ルート（POST）の検証
     * チャットAPIが正しいJSONレスポンスを返すか検証
     */
    public function test_chat_api_endpoint_returns_json()
    {
        $payload = ['message' => 'Test message'];
        $response = $this->postJson('/chat', $payload);
        $response->assertStatus(200)
            ->assertJsonStructure(['response', 'mode']);
    }

    /**
     * /teach ルート（GET）の検証
     * 知識登録フォームが正しく表示されるか検証
     */
    public function test_teach_page_displays_knowledge_registration_form()
    {
        $response = $this->get('/teach');
        $response->assertStatus(200)
            ->assertSee('登録'); // フォームに含まれるテキスト例
    }

    /**
     * /teach ルート（POST）の検証
     * 知識登録処理が成功後、正しくリダイレクトし、セッションに success メッセージがあるか検証
     */
    public function test_knowledge_store_route_redirects()
    {
        $data = [
            'category'      => 'Test Category',
            'title'         => 'Test Title',
            'content'       => 'Test Content',
            'existing_tags' => [],
            'new_tags'      => 'tag1, tag2'
        ];

        $response = $this->post('/teach', $data);
        $response->assertRedirect('/teach');
        $response->assertSessionHas('success');
    }

    /**
     * /knowledge ルートの検証
     * 知識一覧ビューが正しく表示されるか検証
     */
    public function test_knowledge_page_displays_knowledge_list()
    {
        $response = $this->get('/knowledge');
        $response->assertStatus(200)
            ->assertSee('知識'); // 実際の表示内容に合わせて調整
    }
}
