<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();

        $table->string('first_name');
        $table->string('last_name');

        $table->string('email')->unique();
        $table->string('password');

        $table->enum('role', [
            'admin',
            'instructor',
            'student',
            'ojt'
        ])->default('student');

        $table->string('program')->nullable();
        $table->string('year_section')->nullable();

        $table->boolean('is_approved')->default(false);

        $table->timestamp('email_verified_at')->nullable();

        $table->rememberToken();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
