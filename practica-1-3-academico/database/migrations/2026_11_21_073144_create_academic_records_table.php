<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_name');
            $table->unsignedInteger('absences')->default(0);
            $table->decimal('grade', 5, 2);
            $table->string('grade_equivalence', 5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_records');
    }
};