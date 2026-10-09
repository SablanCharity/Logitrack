<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('classroom_id')
                  ->constrained('classrooms')
                  ->onDelete('cascade');

            $table->string('group_name');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_rooms');
    }
};