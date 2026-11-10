<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('student_number', 50)->nullable()->unique()->after('id');
            $table->string('registration_otp_hash')->nullable()->after('password');
            $table->timestamp('registration_otp_expires_at')->nullable()->after('registration_otp_hash');
            $table->unsignedTinyInteger('registration_otp_attempts')->default(0)->after('registration_otp_expires_at');
        });

        Schema::create('student_masterlist_records', function (Blueprint $table): void {
            $table->id();
            $table->string('student_number', 50)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('program')->nullable();
            $table->string('status')->default('pending');
            $table->string('source_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_masterlist_records');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['student_number']);
            $table->dropColumn([
                'student_number',
                'registration_otp_hash',
                'registration_otp_expires_at',
                'registration_otp_attempts',
            ]);
        });
    }
};
