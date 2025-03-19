<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatKnowledgeCategoryTable extends Migration
{
    public function up(): void
    {
        Schema::create('chat_knowledge_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_id')->constrained('chat_knowledge')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('chat_categories')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['knowledge_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_knowledge_category');
    }
}
