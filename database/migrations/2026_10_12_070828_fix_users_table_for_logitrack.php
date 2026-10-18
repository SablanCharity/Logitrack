<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }

            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->after('id');
            }

            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->after('first_name');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'admin',
                    'instructor',
                    'student',
                    'ojt',
                ])->default('student')->after('password');
            }

            if (! Schema::hasColumn('users', 'program')) {
                $table->string('program')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'year_section')) {
                $table->string('year_section')->nullable()->after('program');
            }

            if (! Schema::hasColumn('users', 'is_approved')) {
                $table->boolean('is_approved')
                    ->default(false)
                    ->after('year_section');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [
                'first_name',
                'last_name',
                'role',
                'program',
                'year_section',
                'is_approved',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->after('id');
            }
        });
    }
};
