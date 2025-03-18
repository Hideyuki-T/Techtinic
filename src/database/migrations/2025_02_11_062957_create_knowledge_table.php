<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKnowledgeTable extends Migration
{
    public function up()
    {
        Schema::create('knowledge', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // 知識のタイトル（任意）
            $table->text('content');             // 実際の知識の内容
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('knowledge');
    }
}
