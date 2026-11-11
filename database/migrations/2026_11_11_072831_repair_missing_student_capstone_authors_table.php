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
        if (! Schema::hasTable('student_capstone_authors')) {
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
                $table->unique(
                    ['student_capstone_submission_id', 'student_masterlist_record_id'],
                    'student_capstone_authors_unique',
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep this repair non-destructive so rollback cannot drop existing author data.
    }
};
