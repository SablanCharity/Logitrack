<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archived_capstones', function (Blueprint $table): void {
            $table->json('metadata_image_paths')->nullable()->after('metadata_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('archived_capstones', function (Blueprint $table): void {
            $table->dropColumn('metadata_image_paths');
        });
    }
};
