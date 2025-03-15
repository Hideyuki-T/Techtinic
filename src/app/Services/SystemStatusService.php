<?php

namespace App\Services;

class SystemStatusService
{
    /**
     * オンライン状態かどうかを判定する
     */
    public static function isOnline()
    {
        //configファイルやキャッシュ、DBなどからシステムの状態を取得して判定
        return true;
    }
}
