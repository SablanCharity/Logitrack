<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_capstone_authors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_capstone_submission_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('student_masterlist_record_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_leader')->default(false);
            $table->timestamps();
            $table->unique(['student_capstone_submission_id', 'student_masterlist_record_id'], 'student_capstone_authors_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_capstone_authors');
    }
};
