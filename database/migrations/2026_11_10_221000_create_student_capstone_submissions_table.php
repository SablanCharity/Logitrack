<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_capstone_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('abstract');
            $table->date('submitted_date');
            $table->string('study_path');
            $table->string('manual_path');
            $table->string('system_path');
            $table->string('mentee_form_path');
            $table->string('status')->default('submitted');
            $table->text('return_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_capstone_submissions');
    }
};
