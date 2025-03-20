<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTetrisScoresTable extends Migration
{
    public function up()
    {
        Schema::create('tetris_scores', function (Blueprint $table) {
            $table->id();
            $table->string('player_name', 50);
            $table->integer('score')->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tetris_scores');
    }
}
