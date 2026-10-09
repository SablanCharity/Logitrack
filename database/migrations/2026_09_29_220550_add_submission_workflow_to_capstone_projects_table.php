<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capstone_projects', function (Blueprint $table) {

            // Who submitted the capstone
            $table->foreignId('submitted_by')
                ->nullable()
                ->after('group_room_id')
                ->constrained('users')
                ->nullOnDelete();

            // Instructor/Adviser who verified the metadata
            $table->foreignId('verified_by')
                ->nullable()
                ->after('submitted_by')
                ->constrained('users')
                ->nullOnDelete();

            // Admin who gave final approval
            $table->foreignId('approved_by')
                ->nullable()
                ->after('verified_by')
                ->constrained('users')
                ->nullOnDelete();

            // Dates for each stage
            $table->timestamp('submitted_at')
                ->nullable()
                ->after('status');

            $table->timestamp('verified_at')
                ->nullable()
                ->after('submitted_at');

            $table->timestamp('approved_at')
                ->nullable()
                ->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('capstone_projects', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'submitted_by',
                'verified_by',
                'approved_by',
                'submitted_at',
                'verified_at',
                'approved_at',
            ]);
        });
    }
};