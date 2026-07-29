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
        Schema::create('combo_combo_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->cascadeOnDelete();
            $table->foreignId('combo_item_id')->constrained('combo_items')->cascadeOnDelete();
            $table->integer('quantity')->default(1)->comment('Số lượng thành phần trong combo');
            $table->timestamps();

            $table->unique(['combo_id', 'combo_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_combo_item');
    }
};
