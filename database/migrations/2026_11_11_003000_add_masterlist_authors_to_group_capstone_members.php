<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_capstone_members', function (Blueprint $table): void {
            $table->foreignId('student_masterlist_record_id')
                ->nullable()
                ->after('student_id')
                ->constrained('student_masterlist_records')
                ->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('group_capstone_members', function (Blueprint $table): void {
            $table->dropForeign(['student_masterlist_record_id']);
            $table->dropColumn('student_masterlist_record_id');
            $table->foreignId('student_id')->nullable(false)->change();
        });
    }
};
