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
        Schema::table('reimbursements', function (Blueprint $table) {
            // Make category_id nullable to allow custom categories
            $table->foreignId('category_id')->nullable()->change();
            
            // Add category_name field for custom categories
            $table->string('category_name')->nullable()->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            // Revert category_id to NOT NULL
            $table->foreignId('category_id')->nullable(false)->change();
            
            // Drop category_name field
            $table->dropColumn('category_name');
        });
    }
};
