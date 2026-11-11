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
        if (! Schema::hasTable('group_capstone_submissions')) {
            Schema::create('group_capstone_submissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
                $table->string('program', 20);
                $table->string('title');
                $table->text('abstract');
                $table->string('adviser');
                $table->date('submitted_date');
                $table->string('study_path');
                $table->string('system_path');
                $table->string('document_path');
                $table->string('mentee_form_path');
                $table->string('content_hash', 64)->unique();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('group_capstone_members')) {
            Schema::create('group_capstone_members', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_capstone_submission_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('student_masterlist_record_id')
                    ->nullable()
                    ->constrained('student_masterlist_records')
                    ->cascadeOnDelete();
                $table->boolean('is_leader')->default(false);
                $table->timestamps();
                $table->unique(
                    ['group_capstone_submission_id', 'student_id'],
                    'group_capstone_members_submission_student_unique',
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep this repair non-destructive so rollback cannot drop existing submission data.
    }
};
