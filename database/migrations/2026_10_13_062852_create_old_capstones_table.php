<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_capstones', function (Blueprint $table) {
            $table->id();

            // Extracted and corrected metadata
            $table->string('title');
            $table->longText('abstract')->nullable();
            $table->json('authors')->nullable();
            $table->string('adviser')->nullable();
            $table->string('program')->nullable();
            $table->string('department')->nullable();
            $table->string('institution')->nullable();
            $table->string('publication_date')->nullable();

            // OCR source and extracted text
            $table->string('metadata_image_path')->nullable();
            $table->longText('ocr_text')->nullable();

            // Review and approval workflow
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->text('return_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_capstones');
    }
};
