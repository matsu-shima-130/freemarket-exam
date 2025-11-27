<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            // 誰のコメントか／どの商品へのコメントか
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            // 大量データになったときの高速化用
            $table->unique(['user_id','item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
}
