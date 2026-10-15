<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capstone_projects', function (Blueprint $table) {
            $table->boolean('is_best')->default(false);
            $table->unsignedInteger('views_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('capstone_projects', function (Blueprint $table) {
            $table->dropColumn(['is_best', 'views_count']);
        });
    }
};
