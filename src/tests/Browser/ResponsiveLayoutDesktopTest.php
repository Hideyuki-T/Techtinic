<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ResponsiveLayoutDesktopTest extends DuskTestCase
{
    /**
     * Desktop レイアウトが正しく表示されるか検証するテスト
     *
     * 前提:
     * - ホームページ（'/'）には、デスクトップ表示時にのみ表示される要素（例: サイドバーや特定のナビゲーションバー）がある
     * - ここでは、例として '.desktop-sidebar' というクラスの要素が表示されることを検証
     *
     * @return void
     */
    public function test_responsive_layout_desktop()
    {
        $this->browse(function (Browser $browser) {
            // デスクトップ用の画面サイズにリサイズ（例: 1440 x 900）
            $browser->resize(1440, 900)
                ->visit('/')
                // 例: ヘッダーに "Techtinic Chat" のテキストが含まれているか検証
                ->assertSee('Techtinic Chat')
                // 例: デスクトップ専用のサイドバーが表示されているか検証（※実際のクラス名に合わせて調整してください）
                ->assertVisible('.desktop-sidebar');
        });
    }
}
