<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        if (! Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('first_name')->after('id');
            });
        }

        if (! Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_name')->after('first_name');
            });
        }

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', [
                    'admin',
                    'instructor',
                    'student',
                    'ojt',
                ])->default('student')->after('password');
            });
        }

        if (! Schema::hasColumn('users', 'program')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('program')->nullable()->after('role');
            });
        }

        if (! Schema::hasColumn('users', 'year_section')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('year_section')->nullable()->after('program');
            });
        }

        if (! Schema::hasColumn('users', 'is_approved')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_approved')
                    ->default(false)
                    ->after('year_section');
            });
        }
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
