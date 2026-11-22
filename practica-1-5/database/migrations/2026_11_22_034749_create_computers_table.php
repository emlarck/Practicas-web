<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ej. PC-01
            $table->string('name');
            $table->string('specifications')->nullable();
            $table->string('lab_room')->nullable();
            $table->string('status', 20)->default('disponible'); // 'disponible', 'reservado', 'mantenimiento'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};