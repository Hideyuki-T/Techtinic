<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToKnowledgeTable extends Migration
{
    public function up()
    {
        Schema::table('knowledge', function (Blueprint $table) {
            $table->softDeletes(); // deleted_at カラムを追加
        });
    }

    public function down()
    {
        Schema::table('knowledge', function (Blueprint $table) {
            $table->dropSoftDeletes(); // 削除する際に削除する
        });
    }
}
