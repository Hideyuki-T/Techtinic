<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatKnowledgeTagTable extends Migration
{
    public function up(): void
    {
        Schema::create('chat_knowledge_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_id')->constrained('chat_knowledge')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('chat_tags')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['knowledge_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_knowledge_tag');
    }
}
