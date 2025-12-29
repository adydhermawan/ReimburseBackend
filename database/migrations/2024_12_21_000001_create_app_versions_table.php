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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version'); // e.g., "1.0.1"
            $table->integer('build_number')->default(1);
            $table->string('download_url'); // APK download link
            $table->text('release_notes')->nullable();
            $table->boolean('is_mandatory')->default(false); // Force update?
            $table->boolean('is_active')->default(true); // Current active version
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
