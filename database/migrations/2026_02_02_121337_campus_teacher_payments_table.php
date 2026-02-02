<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('campus_teacher_payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('teacher_id')->constrained('campus_teachers')->cascadeOnDelete();
        $table->foreignId('course_id')->constrained('campus_courses')->cascadeOnDelete();
        $table->foreignId('season_id')->constrained('campus_seasons')->cascadeOnDelete();

        $table->enum('payment_option', ['self', 'delegate', 'renounce'])->nullable();

        // snapshot dades financeres
        $table->string('first_name');
        $table->string('last_name');
        $table->string('fiscal_id'); // DNI / NIE → obligatori sempre
        $table->string('postal_code');
        $table->string('city')->nullable();
        $table->string('fiscal_situation')->nullable();

        $table->string('iban');
      //  $table->enum('needs_payment', ['own_fee', 'ceded_fee', 'waived_fee'])->default('own_fee');

        $table->timestamps();

        $table->unique(['teacher_id', 'course_id', 'season_id']);
   
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_teacher_payments');
    }
};

