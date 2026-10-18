<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capstone_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('capstone_project_id')
                ->nullable()
                ->constrained('capstone_projects')
                ->nullOnDelete();

            $table->string('action', 100);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['capstone_project_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capstone_audits');
    }
};