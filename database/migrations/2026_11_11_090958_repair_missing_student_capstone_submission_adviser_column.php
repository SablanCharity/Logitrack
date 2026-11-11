<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_capstone_submissions')
            || Schema::hasColumn('student_capstone_submissions', 'adviser')) {
            return;
        }

        Schema::table('student_capstone_submissions', function (Blueprint $table): void {
            $table->string('adviser')->nullable()->after('abstract');
        });
    }

    public function down(): void
    {
        // Keep this repair non-destructive so rollback cannot remove adviser data.
    }
};
