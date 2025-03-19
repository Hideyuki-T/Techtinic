<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatKnowledgeTable extends Migration
{
    public function up()
    {
        Schema::create('chat_knowledge', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // 知識のタイトル
            $table->text('content');             // 実際の知識の内容
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_knowledge');
    }
}
