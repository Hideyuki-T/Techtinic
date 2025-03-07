<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageHttpsResponseTest extends TestCase
{
    /**
     * HTTPS通信でホームページが正しくレスポンスを返すか検証するテスト
     *
     * @return void
     */
    public function test_home_page_returns_successful_https_response()
    {
        // HTTPS環境をシミュレートするため、サーバ変数にHTTPS=onを設定
        $response = $this->withServerVariables(['HTTPS' => 'on'])
            ->get('/');

        // HTTPステータス200を検証
        $response->assertStatus(200);

        // オプション: Strict-Transport-Securityヘッダーが存在する場合は検証
        if ($response->headers->has('Strict-Transport-Security')) {
            $this->assertNotEmpty($response->headers->get('Strict-Transport-Security'));
        }
    }
}
