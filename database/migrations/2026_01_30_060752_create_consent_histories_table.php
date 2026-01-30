<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consent_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('season'); // ex: 2025-2026
            $table->string('document_path'); // PDF
            $table->timestamp('accepted_at');
            $table->string('checksum'); // hash del PDF

            $table->timestamps();

            $table->unique(['teacher_id', 'season']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_histories');
    }
};
