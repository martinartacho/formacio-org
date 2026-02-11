<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('teacher_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('dni')->nullable();
            $table->string('email')->nullable(); // Email específico del profesor
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->string('title')->nullable(); // Dr., Prof., etc.
            $table->json('areas')->nullable(); // Áreas de especialización
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->date('hiring_date');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['teacher_code']);
            $table->unique(['user_id']); // Un usuario solo puede tener un perfil de profesor
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_teachers');
    }
};