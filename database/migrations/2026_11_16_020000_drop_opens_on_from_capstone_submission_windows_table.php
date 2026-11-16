<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capstone_submission_windows', function (Blueprint $table): void {
            $table->dropColumn('opens_on');
        });
    }

    public function down(): void
    {
        Schema::table('capstone_submission_windows', function (Blueprint $table): void {
            $table->date('opens_on')->nullable()->after('is_open');
        });
    }
};
