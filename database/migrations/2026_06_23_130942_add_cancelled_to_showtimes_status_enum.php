<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE showtimes MODIFY COLUMN status ENUM('upcoming', 'showing', 'finished', 'cancelled') NOT NULL DEFAULT 'upcoming'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE showtimes MODIFY COLUMN status ENUM('upcoming', 'showing', 'finished') NOT NULL DEFAULT 'upcoming'");
    }
};
