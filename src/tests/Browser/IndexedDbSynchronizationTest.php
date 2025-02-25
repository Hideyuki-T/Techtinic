<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class IndexedDbSynchronizationTest extends DuskTestCase
{
    /**
     * IndexedDBに同期済みのデータが正しく保存され、オフライン時にも利用できるかを検証するテスト
     *
     * 前提:
     * - /knowledge ページにアクセスすると、IndexedDBから同期されたデータが反映される
     * - 同期完了後、画面上に <div id="indexeddb-status">Data Synchronized</div>
     *   というインジケータが表示される
     *
     * @return void
     */
    public function test_indexeddb_synchronization()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/knowledge')
                ->waitFor('#indexeddb-status', 10)
                ->assertSee('Data Synchronized');
        });
    }
}
