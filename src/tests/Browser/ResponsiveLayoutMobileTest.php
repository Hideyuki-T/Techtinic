<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class ResponsiveLayoutMobileTest extends DuskTestCase
{
    /**
     * Mobile レイアウトが正しく表示されるか検証するテスト
     *
     * 前提:
     * - ホームページ ('/') に、モバイル表示用の専用要素（例: .mobile-menu）が実装されている
     * - モバイル用のUIが適切にレンダリングされていることを確認する
     *
     * @return void
     */
    public function test_responsive_layout_mobile()
    {
        $this->browse(function (Browser $browser) {
            // モバイル用の画面サイズにリサイズ（例: 375 x 667）
            $browser->resize(375, 667)
                ->visit('/')
                // 例: モバイル専用のナビゲーションメニューが表示されているか検証
                ->assertVisible('.mobile-menu')
                // 例: ページ内に「Techtinic Chat」のテキストが正しく表示されているか検証
                ->assertSee('Techtinic Chat');
        });
    }
}
