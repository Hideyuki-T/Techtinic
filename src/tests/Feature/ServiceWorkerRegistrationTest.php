<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ServiceWorkerRegistrationTest extends DuskTestCase
{
    /**
     * ブラウザがサービスワーカーを正しく登録しているか検証するテスト
     *
     * 前提: ホームページでサービスワーカー登録完了後、#sw-status 要素に「Service Worker Registered」と表示される
     *
     * @return void
     */
    public function test_service_worker_registration()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                // #sw-status 要素が表示されるのを最大10秒待機
                ->waitFor('#sw-status', 10)
                ->assertSee('Service Worker Registered');
        });
    }
}
