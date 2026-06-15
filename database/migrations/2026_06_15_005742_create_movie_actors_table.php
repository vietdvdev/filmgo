<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movie_actors', function (Blueprint $table) {
            $table->foreignId('movie_id')->comment('Mã phim')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('actor_id')->comment('Mã diễn viên')->constrained('actors')->cascadeOnDelete();
            $table->string('role_name', 100)->nullable()->comment('Tên nhân vật đảm nhận trong phim');
            $table->primary(['movie_id', 'actor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_actors');
    }
};
