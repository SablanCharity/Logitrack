<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');

            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');

            $table->enum('role', [
                'admin',
                'instructor',
                'student',
                'ojt',
            ])->default('student')->after('password');

            $table->string('program')->nullable()->after('role');
            $table->string('year_section')->nullable()->after('program');

            $table->boolean('is_approved')
                ->default(false)
                ->after('year_section');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'role',
                'program',
                'year_section',
                'is_approved',
            ]);

            $table->string('name')->after('id');
        });
    }
};
