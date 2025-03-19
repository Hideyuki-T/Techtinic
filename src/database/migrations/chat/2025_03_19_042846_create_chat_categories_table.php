<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('chat_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // カテゴリー名（ユニーク）
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_categories');
    }
}
