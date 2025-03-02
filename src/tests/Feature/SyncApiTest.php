<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Knowledge;

class SyncApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * /api/sync エンドポイントが正しい JSON 構造と内容を返すか検証するテスト
     *
     * @return void
     */
    public function test_sync_endpoint_returns_valid_json()
    {
        // テスト用に Knowledge モデルのデータを作成
        $knowledge = Knowledge::factory()->create([
            'title'   => 'テスト知識',
            'content' => 'これはテストの内容です。',
        ]);

        // /api/sync エンドポイントに GET リクエストを送信
        $response = $this->getJson('/api/sync');

        // ステータスコードとヘッダーを検証
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            // JSON 構造を検証
            ->assertJsonStructure([
                'knowledge' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        // 他に必要なフィールドがあれば追加
                    ],
                ],
            ]);

        // JSON の内容を検証
        $data = $response->json();
        $this->assertNotEmpty($data['knowledge']);
        $this->assertEquals('テスト知識', $data['knowledge'][0]['title']);
    }

    /**
     * /api/config エンドポイントが、環境変数 SYNC_SERVER_IP に基づいた IP アドレスを返すか検証するテスト
     *
     * @return void
     */
    public function test_config_endpoint_returns_sync_server_ip()
    {
        // 環境変数 SYNC_SERVER_IP が設定されていなければ 'localhost' をデフォルトとする
        $expectedIp = env('SYNC_SERVER_IP', 'localhost');

        // /api/config エンドポイントに GET リクエストを送信
        $response = $this->getJson('/api/config');

        $response->assertStatus(200)
            ->assertJson([
                'sync_server_ip' => $expectedIp,
            ]);
    }
}
