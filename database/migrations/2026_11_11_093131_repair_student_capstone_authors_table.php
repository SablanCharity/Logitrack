
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_capstone_authors')) {
            return;
        }

        Schema::create('student_capstone_authors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_capstone_submission_id')
                ->constrained('student_capstone_submissions')
                ->cascadeOnDelete();

            $table->foreignId('student_masterlist_record_id')
                ->constrained('student_masterlist_records')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_leader')->default(false);

            $table->timestamps();

            $table->unique(
                ['student_capstone_submission_id', 'student_masterlist_record_id'],
                'student_capstone_authors_unique'
            );
        });
    }

    public function down(): void
    {
        // Intentionally preserve this repaired table and its data.
    }
};
