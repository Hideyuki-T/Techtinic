<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * /chat エンドポイントの POST リクエストに対して、正しい JSON レスポンスが返るか検証するテスト
     *
     * @return void
     */
    public function test_chat_endpoint_returns_json_response()
    {
        // ChatService のモックを作成し、期待されるレスポンスを返すように設定
        $expectedResult = [
            'response' => 'テスト結果',
            'mode' => 'default'
        ];

        $chatServiceMock = \Mockery::mock(ChatService::class);
        $chatServiceMock->shouldReceive('processMessage')
            ->once()
            ->with('テストメッセージ')
            ->andReturn($expectedResult);

        // サービスコンテナにモックをバインド
        $this->app->instance(ChatService::class, $chatServiceMock);

        // /chat エンドポイントへ JSON 形式の POST リクエストをシミュレート
        $response = $this->postJson('/chat', ['message' => 'テストメッセージ']);

        // レスポンスの HTTP ステータスと JSON 内容を検証
        $response->assertStatus(200)
            ->assertJson($expectedResult);
    }
}
