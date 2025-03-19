<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatTagsTable extends Migration
{
    public function up(): void
    {
        Schema::create('chat_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // タグ名（ユニーク）
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_tags');
    }
}
