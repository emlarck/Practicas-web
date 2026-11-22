<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_x_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player_o_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('board');
            $table->string('current_turn', 1)->default('X'); // 'X' o 'O'
            $table->string('status', 20)->default('waiting'); // 'waiting', 'playing', 'finished', 'cancelled'
            $table->string('result', 30)->nullable(); // 'win_x', 'win_o', 'draw', 'abandoned', 'cancelled'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};