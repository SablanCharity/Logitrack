<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capstone_projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_room_id')
                  ->constrained('group_rooms')
                  ->onDelete('cascade');

            $table->string('title');
            $table->text('abstract')->nullable();

            $table->string('program');
            $table->string('year_section')->nullable();

            $table->date('publication_date')->nullable();

            $table->string('status')->default('draft');

            $table->timestamps();

            $table->unique('group_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capstone_projects');
    }
};