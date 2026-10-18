<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->timestamp('group_creation_opens_at')->nullable();
            $table->timestamp('group_creation_closes_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn([
                'group_creation_opens_at',
                'group_creation_closes_at',
            ]);
        });
    }
};