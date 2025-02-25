<?php

namespace Tests\Feature;

use Tests\TestCase;

class DockerContainerCommunicationTest extends TestCase
{
    /**
     * techtinic-nginx コンテナとの通信が可能か検証
     *
     * 内部ネットワーク上では、nginxコンテナは通常443ポートで待機していることを想定しています。
     *
     * @return void
     */
    public function test_nginx_container_connection()
    {
        $host = 'techtinic-nginx'; // Docker ネットワーク上の nginx コンテナ名
        $port = 443;              // nginx コンテナ内部のポート
        $timeout = 5;             // タイムアウト（秒）

        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        $this->assertNotFalse($fp, "Failed to connect to {$host} on port {$port}: {$errstr} ({$errno})");
        if ($fp) {
            fclose($fp);
        }
    }

    /**
     * techtinic-db コンテナとの通信が可能か検証
     *
     * PostgreSQL の内部ポートは5432で動作している前提です。
     *
     * @return void
     */
    public function test_postgresql_container_connection()
    {
        $host = 'techtinic-db'; // Docker ネットワーク上の PostgreSQL コンテナ名
        $port = 5432;          // PostgreSQL の内部ポート
        $timeout = 5;

        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        $this->assertNotFalse($fp, "Failed to connect to {$host} on port {$port}: {$errstr} ({$errno})");
        if ($fp) {
            fclose($fp);
        }
    }
}
