<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('avatar_path', 255)->nullable();
        $table->string('postal_code', 10);
        $table->string('address', 255);
        $table->string('building', 255)->nullable();
        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
