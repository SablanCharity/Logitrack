<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_capstone_submissions', function (Blueprint $table): void {
            $table->string('content_hash', 64)->nullable()->unique()->after('abstract');
        });
    }

    public function down(): void
    {
        Schema::table('student_capstone_submissions', function (Blueprint $table): void {
            $table->dropUnique(['content_hash']);
            $table->dropColumn('content_hash');
        });
    }
};
