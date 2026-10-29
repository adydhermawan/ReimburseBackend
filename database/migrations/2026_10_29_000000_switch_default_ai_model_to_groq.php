<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_ai_model')->nullable()->default('qwen/qwen3.8-27b')->change();
        });

        // Move users still on the old Gemini default (or unset) to Groq. Gemini stays selectable as secondary.
        DB::table('users')
            ->whereNull('preferred_ai_model')
            ->orWhere('preferred_ai_model', 'gemini-3.5-flash')
            ->update(['preferred_ai_model' => 'qwen/qwen3.8-27b']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_ai_model')->nullable()->default('gemini-3.5-flash')->change();
        });
    }
};
